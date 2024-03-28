<?php

// Phpunit test environment

return function (array $settings): array {
    $settings['error']['display_error_details'] = true;

    return $settings;
};
