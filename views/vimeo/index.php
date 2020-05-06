<form class="default" action="<?php echo $controller->link_for('vimeo/authorize') ?>" method="post">
    <section>
        <label for="client-id">
            <?php echo dgettext('videos', 'Client-ID (der in Vimeo erstellten App)') ?>
        </label>
        <input type="text" name="client_id" id="client-id"
               value="<?php echo htmlReady(Config::get()->VIMEO_CLIENT_ID) ?>">
    </section>
    <section>
        <label for="client-secret">
            <?php echo dgettext('videos', 'Client Secret (der in Vimeo erstellten App)') ?>
        </label>
        <input type="text" name="client_secret" id="client-secret"
               value="<?php echo htmlReady(Config::get()->VIMEO_CLIENT_SECRET) ?>">
    </section>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(dgettext('videos', 'Bei Vimeo authentifizieren'), 'store') ?>
    </footer>
</form>
