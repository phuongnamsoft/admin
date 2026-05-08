<?php

namespace PNS\Admin\Form\Field;

class UnsupportedLegacyInterventionCallException extends \InvalidArgumentException
{
    public function __construct(
        public readonly string $originalMethod,
        string $reason,
        public readonly string $suggestedReplacement
    ) {
        parent::__construct(sprintf(
            'Unsupported legacy Intervention call [%s]: %s Suggested v3 approach: %s',
            $originalMethod,
            $reason,
            $suggestedReplacement
        ));
    }
}
