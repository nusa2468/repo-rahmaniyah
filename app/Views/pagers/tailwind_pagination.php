<?php $pager->setSurroundCount(2) ?>

<nav aria-label="Page navigation" class="flex items-center justify-between">
    <div class="flex items-center gap-1.5">
        <?php if ($pager->hasPrevious()) : ?>
            <a href="<?= $pager->getFirst() ?>" aria-label="<?= lang('Pager.first') ?>" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:text-sky-600 transition-all shadow-sm">
                <i class="fas fa-angles-left text-[10px]"></i>
            </a>
            <a href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:text-sky-600 transition-all shadow-sm">
                <i class="fas fa-chevron-left text-[10px]"></i>
            </a>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <a href="<?= $link['uri'] ?>" class="w-9 h-9 flex items-center justify-center rounded-xl text-xs font-bold border transition-all shadow-sm <?= $link['active'] ? 'bg-sky-600 text-white border-sky-600 shadow-sky-200' : 'bg-white dark:bg-gray-800 text-gray-600 dark:text-gray-400 border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 hover:text-sky-600' ?>">
                <?= $link['title'] ?>
            </a>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <a href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:text-sky-600 transition-all shadow-sm">
                <i class="fas fa-chevron-right text-[10px]"></i>
            </a>
            <a href="<?= $pager->getLast() ?>" aria-label="<?= lang('Pager.last') ?>" class="w-9 h-9 flex items-center justify-center rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-gray-500 hover:text-sky-600 transition-all shadow-sm">
                <i class="fas fa-angles-right text-[10px]"></i>
            </a>
        <?php endif ?>
    </div>
</nav>