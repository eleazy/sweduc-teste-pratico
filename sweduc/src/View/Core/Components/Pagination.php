<div class="w-full sm:w-auto">
    <ul class="flex bg-gray-300 rounded py-2 justify-between mb-0">
        <?php if ($current - 1 > 0) : ?>
            <li>
                <a
                    class="m-0.5 p-2 rounded text-gray-700 hover:bg-themeColor hover:text-white"
                    href="#"
                    onclick="sweduc.carregarUrl('<?=$urlBuilder($current - 1)?>')"
                >
                    &laquo;
                </a>
            </li>
        <?php endif ?>

        <?php foreach ($this->pagination($current, $total) as $pagina) : ?>
            <li>
                <a
                    class="
                        m-0.5 p-2
                        rounded
                        hover:bg-themeColor
                        hover:text-white
                        hover:no-underline
                        <?=$pagina == $current ? 'bg-themeColor text-white' : 'text-gray-700'?>
                    "
                    href="#"
                    onclick="sweduc.carregarUrl('<?=$urlBuilder($pagina)?>')"
                >
                    <?=$pagina?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($current + 1 < $total) : ?>
            <li>
                <a
                    class="m-0.5 p-2 rounded text-gray-700 hover:bg-themeColor hover:text-white"
                    href="#"
                    onclick="sweduc.carregarUrl('<?=$urlBuilder($current + 1)?>')"
                >
                    &raquo;
                </a>
            </li>
        <?php endif ?>
    </ul>
</div>
