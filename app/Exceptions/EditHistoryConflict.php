<?php

namespace App\Exceptions;

use RuntimeException;

class EditHistoryConflict extends RuntimeException
{
    public static function contentChanged(): self
    {
        return new self(
            'No se aplicó el cambio porque otra persona modificó este contenido. Recarga la página, revisa la versión actual y vuelve a intentarlo.'
        );
    }
}
