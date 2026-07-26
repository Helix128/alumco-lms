import importlib.util
import importlib.machinery
import pathlib
import subprocess
import unittest
from unittest import mock


ROOT = pathlib.Path(__file__).resolve().parents[1]
SCRIPT = ROOT / "scripts" / "docker-network-check"
SPEC = importlib.util.spec_from_loader(
    "docker_network_check",
    importlib.machinery.SourceFileLoader("docker_network_check", str(SCRIPT)),
)
assert SPEC and SPEC.loader
CHECK = importlib.util.module_from_spec(SPEC)
SPEC.loader.exec_module(CHECK)


def bridge(gateway="172.16.0.1"):
    config = {"Subnet": "172.16.0.0/24"}
    if gateway is not None:
        config["Gateway"] = gateway
    return {
        "Name": "bridge",
        "Driver": "bridge",
        "Options": {"com.docker.network.bridge.name": "docker0"},
        "IPAM": {"Config": [config]},
    }


def link(*, up=True, address="172.16.0.1"):
    return {
        "flags": ["BROADCAST", "MULTICAST"] + (["UP"] if up else []),
        "addr_info": [{"family": "inet", "local": address}],
    }


class DefaultBridgeTests(unittest.TestCase):
    def assert_repairable(self, networks, state=None):
        patcher = mock.patch.object(CHECK, "interface_state", return_value=state)
        with patcher, self.assertRaises(SystemExit) as raised:
            CHECK.check_default_bridge(networks)
        self.assertEqual(CHECK.EXIT_DEFAULT_BRIDGE, raised.exception.code)

    def test_healthy_bridge(self):
        with mock.patch.object(CHECK, "interface_state", return_value=link()):
            CHECK.check_default_bridge([bridge()])

    def test_missing_bridge_is_repairable(self):
        self.assert_repairable([])

    def test_bridge_without_gateway_is_repairable(self):
        self.assert_repairable([bridge(None)])

    def test_bridge_administratively_down_is_repairable(self):
        self.assert_repairable([bridge()], link(up=False))

    def test_bridge_without_gateway_address_is_repairable(self):
        self.assert_repairable([bridge()], link(address="172.16.0.2"))

    def test_failed_container_egress_is_not_repairable(self):
        with mock.patch.dict(
            CHECK.os.environ, {"LMS_NETWORK_CHECK_CONTAINER_PROBE": "timeout"}, clear=False
        ), self.assertRaises(SystemExit) as raised:
            CHECK.check_container_connectivity()
        self.assertEqual(CHECK.EXIT_CONTAINER_EGRESS, raised.exception.code)

    def test_failed_buildkit_egress_has_dedicated_exit(self):
        with mock.patch.dict(
            CHECK.os.environ, {"LMS_NETWORK_CHECK_BUILDKIT_PROBE": "DNS timeout"}, clear=False
        ), self.assertRaises(SystemExit) as raised:
            CHECK.check_buildkit_connectivity()
        self.assertEqual(CHECK.EXIT_BUILDKIT_EGRESS, raised.exception.code)

    def test_buildkit_probe_uses_default_network_and_cacheonly_output(self):
        completed = subprocess.CompletedProcess([], 0, "", "")
        with mock.patch.dict(
            CHECK.os.environ,
            {"LMS_DOCKER_COMMAND": "docker", "LMS_NETWORK_CHECK_BUILDKIT_PROBE": ""},
            clear=False,
        ), mock.patch.object(CHECK.subprocess, "run", return_value=completed) as run:
            del CHECK.os.environ["LMS_NETWORK_CHECK_BUILDKIT_PROBE"]
            CHECK.check_buildkit_connectivity()

        command = run.call_args.args[0]
        self.assertEqual(command[:3], ["docker", "buildx", "build"])
        self.assertIn("--no-cache", command)
        self.assertEqual(command[command.index("--network") + 1], "default")
        self.assertEqual(command[command.index("--output") + 1], "type=cacheonly")
        self.assertIn("cat /etc/resolv.conf", run.call_args.kwargs["input"])

    def test_buildkit_failure_prints_builder_and_sandbox_diagnostics(self):
        failed = subprocess.CompletedProcess([], 1, "# cat /etc/resolv.conf\nnameserver 1.1.1.1", "DNS failed")
        inspected = subprocess.CompletedProcess([], 0, "Name: default\nStatus: running", "")
        with mock.patch.dict(
            CHECK.os.environ, {"LMS_DOCKER_COMMAND": "docker"}, clear=False
        ), mock.patch.object(
            CHECK.subprocess, "run", side_effect=[failed, inspected]
        ), mock.patch("sys.stderr") as stderr, self.assertRaises(SystemExit) as raised:
            CHECK.check_buildkit_connectivity()

        self.assertEqual(CHECK.EXIT_BUILDKIT_EGRESS, raised.exception.code)
        rendered = " ".join(str(call) for call in stderr.write.call_args_list)
        self.assertIn("builder activo", rendered)
        self.assertIn("DNS failed", rendered)


class ConnManRouteContaminationTests(unittest.TestCase):
    def test_healthy_physical_default_and_selected_route(self):
        CHECK.check_connman_route_contamination(
            [
                "default via 192.168.1.1 dev enp2s0 proto dhcp src 192.168.1.87 metric 100",
                "169.254.0.0/16 dev enp2s0 scope link metric 1000",
            ],
            "1.1.1.1 via 192.168.1.1 dev enp2s0 src 192.168.1.87 uid 1000",
        )

    def test_real_connman_pollution_fixture_has_dedicated_exit(self):
        routes = [
            "default dev veth7578720 scope link src 169.254.17.42 metric 205",
            "0.0.0.0 dev veth9123456 scope link proto boot src 169.254.21.8 metric 206",
            "169.254.0.0/16 dev veth7578720 proto kernel scope link src 169.254.17.42",
            "default via 192.168.1.1 dev enp2s0 proto dhcp src 192.168.1.87 metric 100",
        ]
        selected = "1.1.1.1 dev veth7578720 src 169.254.17.42 uid 1000"

        with self.assertRaises(SystemExit) as raised:
            CHECK.check_connman_route_contamination(routes, selected)

        self.assertEqual(CHECK.EXIT_CONNMAN_ROUTE, raised.exception.code)

    def test_selected_virtual_route_is_rejected_without_default_or_boot_marker(self):
        with self.assertRaises(SystemExit) as raised:
            CHECK.check_connman_route_contamination(
                ["default via 192.168.1.1 dev enp2s0"],
                "1.1.1.1 dev br-c64d7a6cd2ae src 169.254.80.4",
            )
        self.assertEqual(CHECK.EXIT_CONNMAN_ROUTE, raised.exception.code)

    def test_regular_docker_subnet_route_is_not_host_route_contamination(self):
        CHECK.check_connman_route_contamination(
            [
                "default via 192.168.1.1 dev enp2s0",
                "172.30.250.0/27 dev br-c64d7a6cd2ae proto kernel scope link src 172.30.250.1",
            ],
            "1.1.1.1 via 192.168.1.1 dev enp2s0 src 192.168.1.87",
        )


class ProductionRepairTests(unittest.TestCase):
    def bash(self, body):
        script = f"""
set -euo pipefail
set -- help
source ./lms >/dev/null
{body}
"""
        return subprocess.run(
            ["bash", "-c", script],
            cwd=ROOT,
            text=True,
            capture_output=True,
        )

    def test_nonrepairable_error_never_touches_docker(self):
        result = self.bash(
            """
network_check_raw() { return 14; }
sudo() { echo "sudo must not run" >&2; return 99; }
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 14 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertNotIn("sudo must not run", result.stderr)

    def test_buildkit_error_never_touches_systemctl(self):
        result = self.bash(
            """
network_check_raw() { return 22; }
sudo() { echo "sudo must not run" >&2; return 99; }
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 22 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertNotIn("sudo must not run", result.stderr)

    def test_connman_route_error_never_touches_systemctl(self):
        result = self.bash(
            """
network_check_raw() { return 23; }
sudo() { echo "sudo must not run" >&2; return 99; }
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 23 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertNotIn("sudo must not run", result.stderr)

    def test_failure_enumerating_containers_does_not_repair(self):
        result = self.bash(
            """
network_check_raw() { return 20; }
sudo() {
    if [ "$1 $2 $3" = "docker ps -q" ]; then return 7; fi
    echo "unexpected repair command: $*" >&2
    return 99
}
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 20 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("no se pudo determinar", result.stdout)
        self.assertNotIn("unexpected repair command", result.stderr)

    def test_repairs_automatically_without_running_containers_and_rechecks_once(self):
        result = self.bash(
            """
checks=0
network_check_raw() {
    checks=$((checks + 1))
    if [ "$checks" -eq 1 ]; then return 20; fi
    return 0
}
sudo() {
    if [ "$1 $2 $3" = "docker ps -q" ]; then return 0; fi
    return 0
}
ip() { return 1; }
network_check prod
[ "$checks" -eq 2 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("No hay contenedores activos", result.stdout)

    def test_noninteractive_session_refuses_to_interrupt_containers(self):
        result = self.bash(
            """
network_check_raw() { return 20; }
sudo() {
    if [ "$1 $2 $3" = "docker ps -q" ]; then echo abc123; return 0; fi
    if [ "$1 $2" = "docker ps" ]; then echo mysql; return 0; fi
    echo "unexpected repair command: $*" >&2
    return 99
}
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 20 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("sesión no interactiva", result.stdout)
        self.assertNotIn("unexpected repair command", result.stderr)

    def test_rejected_confirmation_does_not_repair(self):
        result = self.bash(
            """
network_check_raw() { return 20; }
confirm_container_interruption() { echo "rechazado"; return 1; }
sudo() {
    if [ "$1 $2 $3" = "docker ps -q" ]; then echo abc123; return 0; fi
    if [ "$1 $2" = "docker ps" ]; then echo mysql; return 0; fi
    echo "unexpected repair command: $*" >&2
    return 99
}
set +e
network_check prod
status=$?
set -e
[ "$status" -eq 20 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("rechazado", result.stdout)
        self.assertNotIn("unexpected repair command", result.stderr)

    def test_accepted_confirmation_repairs_and_rechecks(self):
        result = self.bash(
            """
checks=0
network_check_raw() {
    checks=$((checks + 1))
    if [ "$checks" -eq 1 ]; then return 20; fi
    return 0
}
confirm_container_interruption() { return 0; }
sudo() {
    case "$*" in
        "docker ps -q") echo abc123; return 0 ;;
        "docker ps --format {{.Names}}") echo mysql; return 0 ;;
        "systemctl stop docker.service docker.socket") return 0 ;;
        "systemctl start docker.socket docker.service") return 0 ;;
        "docker inspect abc123") return 0 ;;
        "docker inspect --format {{.State.Running}} abc123") echo true; return 0 ;;
        *) echo "unexpected: $*" >&2; return 99 ;;
    esac
}
ip() { return 1; }
network_check prod
[ "$checks" -eq 2 ]
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("Repitiendo el chequeo", result.stdout)

    def test_repair_restores_a_previously_running_container(self):
        result = self.bash(
            """
sudo() {
    case "$*" in
        "systemctl stop docker.service docker.socket") return 0 ;;
        "systemctl start docker.socket docker.service") return 0 ;;
        "docker inspect abc123") return 0 ;;
        "docker inspect --format {{.State.Running}} abc123") echo false; return 0 ;;
        "docker start abc123") echo abc123; return 0 ;;
        *) echo "unexpected: $*" >&2; return 99 ;;
    esac
}
ip() { return 1; }
repair_default_bridge abc123
"""
        )
        self.assertEqual(0, result.returncode, result.stderr)
        self.assertIn("Restaurando contenedor abc123", result.stdout)


if __name__ == "__main__":
    unittest.main()
