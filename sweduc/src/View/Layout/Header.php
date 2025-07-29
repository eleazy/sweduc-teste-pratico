<div class="bg-blue-900">
    <nav class="flex items-center container mx-auto px-3">
        <div class="p-2">
            <div class="bg-white rounded">
                <img
                    src="<?="/clientes/{$_SERVER['CLIENTE']}/logo.png"?>"
                    class="h-10"
                    alt=""
                    loading="lazy"
                >
            </div>
        </div>

        <span id="header-client-name" class="text-white text-xl p-2">
            <?=$title?>
        </span>
    </nav>
</div>
