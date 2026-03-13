<?php if (!empty($breadcrumb)): ?>

    <!-- BREADCRUMB NAVIGATION -->
    <nav class="flex items-center text-sm text-gray-600 dark:text-gray-300 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm w-fit ml-0.5 mt-5">

        <a href="/rkd-cafe/resources/views/dashboard/<?= $_SESSION['role'] ?>.php"
            class="flex items-center hover:text-amber-600 transition">

            <i class="fa-solid fa-house text-gray-400 hover:text-gray-600 mr-2"></i>

        </a>

        <?php foreach ($breadcrumb as $index => $item): ?>

            <i class="fa-solid fa-chevron-right text-xs mx-2 text-gray-400"></i>

            <?php $isLast = $index === count($breadcrumb) - 1; ?>

            <?php if (!$isLast): ?>

                <a
                    href="<?= $item['url'] ?? '#' ?>"
                    class="hover:text-amber-600 transition font-medium">

                    <?= $item['title'] ?>

                </a>

            <?php else: ?>

                <span class="font-semibold text-gray-800 dark:text-gray-200">

                    <?= $item['title'] ?>

                </span>

            <?php endif; ?>

        <?php endforeach; ?>

    </nav><?php endif; ?>