<?php

$title = $title ?? 'Chart';
$chartId = $chartId ?? 'chart';
$height = $height ?? 'h-64';

?>

<div class="chart-card bg-white dark:bg-gray-800 rounded-xl shadow relative flex flex-col">

    <!-- HEADER -->
    <div class="flex items-start justify-between px-6 pt-6">

        <div>
            <h2 class="font-semibold text-sm tracking-wide">
                <?= htmlspecialchars($title) ?>
            </h2>

            <p class="text-xs text-gray-400 mt-1">
                Analytics Overview
            </p>
        </div>

        <div class="flex items-center gap-3">

            <!-- download -->
            <button
                class="text-gray-400 hover:text-blue-500 transition cursor-pointer"
                onclick="downloadChart('<?= htmlspecialchars($chartId) ?>')">

                <i class="fa-solid fa-download"></i>

            </button>

            <!-- refresh -->
            <button
                class="text-gray-400 hover:text-blue-500 transition cursor-pointer"
                onclick="refreshChart('<?= htmlspecialchars($chartId) ?>')">

                <i class="fa-solid fa-rotate-right"></i>

            </button>

            <!-- zoom -->
            <button
                class="chart-expand text-gray-400 hover:text-blue-500 transition cursor-pointer"
                onclick="expandChart(this)">

                <i class="fa-solid fa-up-right-and-down-left-from-center"></i>

            </button>

        </div>

    </div>

    <!-- BODY -->
    <div class="px-6 pt-4 flex-1">

        <div class="<?= $height ?> chart-body">
            <canvas id="<?= htmlspecialchars($chartId) ?>"></canvas>
        </div>

    </div>

    <!-- INSIGHT PANEL -->
    <div class="px-6 pb-6">

        <div class="text-xs text-gray-500 dark:text-gray-400 border-t pt-4">

            <span class="font-semibold flex items-center gap-1">
                <i class="fa-solid fa-lightbulb text-yellow-400"></i>
                Insight:
            </span>
            <span class="chart-insight">
                <?= htmlspecialchars($insight ?? "Belum ada insight tersedia.") ?>
            </span>

        </div>

    </div>

</div>