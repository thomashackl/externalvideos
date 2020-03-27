<form class="default" id="add-media-file" action="<?php echo $controller->link_for('media/store') ?>" method="post">
    <section>
        <label for="url">
            Adresse der Datei
        </label>
        <input type="url" name="url" id="url" maxlength="2048" value="<?php echo $medium->sharelink ?>">
    </section>
    <section>
        <label for="title">
            Titel
        </label>
        <input type="text" name="title" id="title" maxlength="255" :value="<?php echo $medium->title ?>">
    </section>
    <footer data-dialog-button>
        <?php if (!$medium->isNew()) : ?>
            <input type="hidden" name="id" :value="<?php echo $medium->id ?>">
        <?php endif ?>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('media'),
            ['data-dialog-close' => true]) ?>
    </footer>
</form>
