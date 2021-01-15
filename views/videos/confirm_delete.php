<form class="default" action="<?= $controller->link_for('videos/delete', $video->id) ?>" method="post">
    <section>
        <?= sprintf(
                dgettext('videos', 'Soll das Video "%s" wirklich gelöscht werden?'),
                $video->title) ?>
    </section>
    <?php if ($video->type == 'vimeo') : ?>
        <input type="checkbox" name="delete_vimeo" id="delete-vimeo" value="1"
               <?= $video->hasMoreReferences() ? 'disabled' : 'checked' ?>>
        <label class="undecorated" for="delete-vimeo">
            <?php if ($video->hasMoreReferences()) : ?>
                <span style="text-decoration: line-through">
            <?php endif ?>
                <?= dgettext('videos', 'Video auch in Vimeo löschen') ?>
            <?php if ($video->hasMoreReferences()) : ?>
                </span>
                <br>
                <strong>
                    <?= dgettext('videos', 'Das Video kann nicht in Vimeo gelöscht werden, da es ' .
                    'noch anderen Stud.IP-Veranstaltungen oder -Einrichtungen zugeordnet ist.') ?>
                </strong>
            <?php endif ?>
        </label>
    <?php endif ?>
    <footer data-dialog-button>
        <?= CSRFProtection::tokenTag() ?>
        <?= Studip\Button::createAccept(_('Löschen'), 'do_delete') ?>
        <?= Studip\LinkButton::createCancel(_('Abbrechen'), $controller->link_for('videos'),
            ['data-dialog-close' => true]) ?>
    </footer>
</form>
