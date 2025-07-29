<?php if (!isset($show) || $show === true) : ?>
    <?php if ($root) : ?>
        <?=$this->eif('<ul class="mainOption">', $root)?>
    <?php else : ?>
        <?=$this->eif('<ul>', $root)?>
    <?php endif; ?>
    <li>
        <a
            <?php if (empty($mode)) : ?>
                href="#<?=$this->slugify(str_replace('.php', '', $link ?? ''))?>"
                <?=$this->eif("onClick=\"sweduc.carregarUrl('" . ($link ?? '') . "');\"", !empty($link))?>
            <?php elseif ($mode == 'anchor') : ?>
                href="<?=$link?>"
                target="_blank"
            <?php elseif ($mode == 'app') : ?>
                href="/#/<?=$link?>"
                onclick="document.getElementById('conteudo').innerHTML = null;"
            <?php endif ?>
        >
            <?=$root ? "<b>" . $label . "</b>" : $label?>
        </a>

        <?php if (isset($children) && (is_countable($children) ? count($children) : 0)) : ?>
            <ul class="subOption">
                <?php foreach ($children as $child) : ?>
                    <?php $this->insert('Core/Components/MenuItem', $child + [ 'root' => false ]) ?>
                <?php endforeach ?>
            </ul>
        <?php endif ?>
    </li>
    <?=$this->eif('</ul>', $root)?>
<?php endif ?>
