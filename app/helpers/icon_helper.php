<?php

function iconSpacing($menu)
{
    // jika menu memiliki children berarti ada chevron
    if (isset($menu['children'])) {
        return "ml-1";
    }

    return "";
}
