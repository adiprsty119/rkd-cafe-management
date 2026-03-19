<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

function generateInitialAvatar(string $name): string
{
    $initials = strtoupper(substr($name, 0, 1));

    $bg = substr(md5($name), 0, 6);

    return "https://ui-avatars.com/api/?name=$initials&background=$bg&color=fff&size=256";
}
