<?php

if (!defined('APP_INIT')) {
    exit('No direct access allowed');
}

/**
 * Base Button (Professional UI Core)
 */
function btnBase($onClick, $icon, $colorClasses, $tooltip = '', $size = 'md', $extra = '', $inner = null)
{
    $sizeClass = match ($size) {
        'sm' => 'w-8 h-8 text-xs',
        'lg' => 'w-11 h-11 text-base',
        default => 'w-9 h-9 text-sm',
    };

    $content = $inner ?? '<i class="fa-solid ' . $icon . '"></i>';

    return '
    <button 
        @click="' . $onClick . '"
        ' . $extra . '
        class="group relative flex items-center justify-center ' . $sizeClass . '
               rounded-xl ' . $colorClasses . '
               transition-all duration-200 ease-out
               hover:scale-105 active:scale-95
               focus:outline-none focus:ring-2 focus:ring-offset-1 focus:ring-amber-400
               shadow-sm hover:shadow-md">

        ' . $content . '

        ' . ($tooltip ? '
        <!-- TOOLTIP -->
        <span 
            class="absolute bottom-full mb-2 px-2 py-1 text-[10px] rounded-md
                   bg-gray-900 text-white whitespace-nowrap
                   opacity-0 translate-y-1 scale-95
                   group-hover:opacity-100 group-hover:translate-y-0 group-hover:scale-100
                   transition-all duration-150 pointer-events-none shadow-lg">
            ' . $tooltip . '
        </span>' : '') . '

    </button>';
}

/**
 * View Button
 */
function btnView($onClick, $tooltip = 'View', $size = 'md')
{
    return btnBase(
        $onClick,
        'fa-eye',
        'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600 cursor-pointer',
        $tooltip,
        $size
    );
}

/**
 * Edit Button
 */
function btnEdit($onClick, $tooltip = 'Edit', $size = 'md')
{
    return btnBase(
        $onClick,
        'fa-pen',
        'bg-blue-50 text-blue-600 hover:bg-blue-100 dark:bg-blue-500/10 dark:text-blue-400 dark:hover:bg-blue-500/20 cursor-pointer',
        $tooltip,
        $size
    );
}

/**
 * Delete / Disable Button (Smart UX 🔥)
 */
function btnDelete($onClick, $tooltip = 'Delete', $size = 'md')
{
    $extra = '
        :disabled="deletingId === item.id"
        :class="deletingId === item.id 
            ? \'opacity-50 cursor-not-allowed\' 
            : \'cursor-pointer\'"
    ';

    $inner = '
        <!-- NORMAL DELETE -->
        <i x-show="!item.used && deletingId !== item.id" 
           class="fa-solid fa-trash"></i>

        <!-- LOADING -->
        <i x-show="deletingId === item.id" 
           class="fa-solid fa-spinner fa-spin"></i>

        <!-- USED (LOCKED / DISABLE MODE) -->
        <i x-show="item.used && deletingId !== item.id" 
           class="fa-solid fa-ban"></i>

        <!-- TOOLTIP DINAMIS -->
        <span 
            class="absolute bottom-full mb-2 px-2 py-1 text-[10px] rounded-md
                   bg-gray-900 text-white whitespace-nowrap
                   opacity-0 translate-y-1 scale-95
                   group-hover:opacity-100 group-hover:translate-y-0 group-hover:scale-100
                   transition-all duration-150 pointer-events-none shadow-lg">

            <span x-text="
                item.used 
                ? \'Disable (data already used)\' 
                : \'Delete item\'
            "></span>
        </span>
    ';

    return btnBase(
        $onClick,
        '',
        'bg-red-50 text-red-600 hover:bg-red-100 
         dark:bg-red-500/10 dark:text-red-400 dark:hover:bg-red-500/20',
        '',
        $size,
        $extra,
        $inner
    );
}

/**
 * Enable Button
 */
function btnEnable($onClick, $tooltip = 'Enable', $size = 'md')
{
    return btnBase(
        $onClick,
        'fa-rotate-left',
        'bg-green-50 text-green-600 hover:bg-green-100 dark:bg-green-500/10 dark:text-green-400 dark:hover:bg-green-500/20 cursor-pointer',
        $tooltip,
        $size
    );
}

/**
 * Action Group (Improved Layout)
 */
function btnActionGroup($editClick = null, $deleteClick = null, $viewClick = null, $enableClick = null)
{
    $buttons = '';

    if ($viewClick) {
        $buttons .= btnView($viewClick);
    }

    if ($editClick) {
        $buttons .= btnEdit($editClick);
    }

    // 🔥 CONDITIONAL DELETE / ENABLE
    if ($deleteClick) {

        $buttons .= '
        <template x-if="item.status === \'active\'">
            ' . btnDelete($deleteClick) . '
        </template>
        ';
    }

    if ($enableClick) {

        $buttons .= '
        <template x-if="item.status === \'inactive\'">
            ' . btnEnable($enableClick) . '
        </template>
        ';
    }

    return '
    <div class="flex items-center gap-2 bg-gray-50 dark:bg-gray-700/50 px-2 py-1 rounded-xl shadow-inner">
        ' . $buttons . '
    </div>';
}
