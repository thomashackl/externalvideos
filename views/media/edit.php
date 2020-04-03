<form class="default" id="add-media-file" action="<?php echo $controller->link_for('media/store',
        $medium->isNew() ? null : $medium->id) ?>" method="post">
    <fieldset>
        <legend>
            <?php echo dgettext('mediacontent', 'Grunddaten') ?>
        </legend>
        <section>
            <label for="url">
                Adresse der Datei
            </label>
            <input type="url" name="url" id="url" maxlength="2048" value="<?php echo htmlReady($medium->url) ?>">
        </section>
        <section>
            <label for="title">
                Titel
            </label>
            <input type="text" name="title" id="title" maxlength="255" value="<?php echo htmlReady($medium->title) ?>">
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?php echo dgettext('mediacontent', 'Sichtbarkeit') ?>
        </legend>
        <section class="col-3">
            <label for="visible-from" class="undecorated">
                <?php echo dgettext('mediacontent', 'von') ?>
            </label>
            <input type="text" name="visible_from" id="visible-from" maxlength="15"
                   placeholder="<?php echo dgettext('mediacontent', 'unbegrenzt') ?>"
                   value="<?php echo $medium->visible_from ? $medium->visible_from->format('d.m.Y H:i') : '' ?>"
                   data-datetime-picker>
        </section>
        <section class="col-3">
            <label for="visible-until" class="undecorated">
                <?php echo dgettext('mediacontent', 'bis') ?>
            </label>
            <input type="text" name="visible_until" id="visible-until" maxlength="15"
                   placeholder="<?php echo dgettext('mediacontent', 'unbegrenzt') ?>"
                   value="<?php echo $medium->visible_until ? $medium->visible_until->format('d.m.Y H:i') : '' ?>"
                   data-datetime-picker='{">=":"#visible-from"}'>
        </section>
    </fieldset>
    <?php if (count($dates) > 0) : ?>
        <fieldset>
            <legend>
                <?php echo dgettext('mediacontent', 'Zuordnung zu Veranstaltungsterminen') ?>
            </legend>
            <section>
                <label for="visible-until">
                    <?php echo dgettext('mediacontent', 'Vorhandene Termine') ?>
                </label>
                <select name="dates[]" class="nested-select">
                    <option value="">
                        -- <?php echo dgettext('mediacontent', 'keinem Termin zuordnen') ?> --
                    </option>
                    <?php foreach ($dates as $id => $name) : ?>
                        <option value="<?php echo htmlReady($id) ?>"<?php echo in_array($id, $selected_dates) ? 'selected' : '' ?>>
                            <?php echo htmlReady($name) ?>
                        </option>
                    <?php endforeach ?>
                </select>
            </section>
        </fieldset>
    <?php endif ?>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Speichern'), 'store') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('media'),
            ['data-dialog-close' => true]) ?>
    </footer>
</form>
