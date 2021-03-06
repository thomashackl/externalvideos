<form class="default" action="<?php echo $controller->link_for('videos/do_import_vimeo') ?>" method="post">
    <fieldset>
        <legend>
            <?php echo dgettext('videos', 'Vimeo') ?>
        </legend>
        <section>
            <label for="url">
                <?php echo dgettext('videos', 'Videolink') ?>
            </label>
            <input type="url" name="url" id="url" maxlength="2048" value="">
        </section>
    </fieldset>
    <fieldset>
        <legend>
            <?php echo dgettext('videos', 'Sichtbarkeit') ?>
        </legend>
        <section class="col-3">
            <label for="visible-from" class="undecorated">
                <?php echo dgettext('videos', 'von') ?>
            </label>
            <input type="text" name="visible_from" id="visible-from" maxlength="15"
                   placeholder="<?php echo dgettext('videos', 'unbegrenzt') ?>"
                   value="" data-datetime-picker>
        </section>
        <section class="col-3">
            <label for="visible-until" class="undecorated">
                <?php echo dgettext('videos', 'bis') ?>
            </label>
            <input type="text" name="visible_until" id="visible-until" maxlength="15"
                   placeholder="<?php echo dgettext('videos', 'unbegrenzt') ?>"
                   value="" data-datetime-picker='{">=":"#visible-from"}'>
        </section>
    </fieldset>
    <?php if (count($dates) > 0) : ?>
        <fieldset>
            <legend>
                <?php echo dgettext('videos', 'Zuordnung zu Veranstaltungsterminen') ?>
            </legend>
            <section>
                <label for="visible-until">
                    <?php echo dgettext('videos', 'Vorhandene Termine') ?>
                </label>
                <select name="dates[]" class="nested-select">
                    <option value="">
                        -- <?php echo dgettext('videos', 'keinem Termin zuordnen') ?> --
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
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('videos'),
            ['data-dialog-close' => true]) ?>
    </footer>
</form>
