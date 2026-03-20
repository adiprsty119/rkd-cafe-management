<?php if (!empty($breadcrumb)): ?>

    <nav class="flex items-center text-sm text-gray-500 bg-white dark:bg-gray-800 px-4 py-2 rounded-lg shadow-sm w-fit ml-0.5 mt-5">

        <!-- HOME -->
        <a href="<?= htmlspecialchars(getDashboardUrl(), ENT_QUOTES, 'UTF-8') ?>"
            class="flex items-center hover:text-amber-600 transition">

            <i class="fa-solid fa-house text-gray-400 hover:text-gray-600 mr-2"></i>

        </a>

        <?php foreach ($breadcrumb as $index => $item): ?>

            <i class="fa-solid fa-chevron-right text-xs mx-2 text-gray-400"></i>

            <?php $isLast = $index === count($breadcrumb) - 1; ?>

            <?php if (!$isLast): ?>

                <a
                    href="<?= htmlspecialchars($item['url'] ?? '#', ENT_QUOTES, 'UTF-8') ?>"
                    class="hover:text-amber-600 transition font-medium">

                    <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>

                </a>

            <?php else: ?>

                <span class="font-semibold text-gray-800 dark:text-gray-200">

                    <?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?>

                </span>

            <?php endif; ?>

        <?php endforeach; ?>

    </nav>

<?php endif; ?>