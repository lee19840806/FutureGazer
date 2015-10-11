<div class="container-fluid" style="padding-left: 40px; padding-right: 40px;">
    <div class="row">
        <div class="col-lg-2">
            <?= $this->element('left_menu') ?>
        </div>
        <div class="col-lg-10">
            <div class="row">
                <div class="col-lg-12">
                    <h4><strong>Build maturation curves</strong></h4>
                    <hr>
                    <?= $this->Flash->render() ?>
                    <table class="table table-hover table-striped">
                        <thead>
                            <tr class="success">
                                <th><?= $this->Paginator->sort('name', 'Manage') ?></th>
                                <th><?= $this->Paginator->sort('name', 'Name') ?></th>
                                <th><?= $this->Paginator->sort('created', 'Created') ?></th>
                                <th><?= $this->Paginator->sort('description', 'Description') ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <tr>
                                    <td>
                                        <?= $this->Html->link('Build curves', ['controller' => 'MaturationCurves', 'action' => 'build_maturation_curves', $file->id]); ?>
                                    </td>
                                    <td><?= h($file->name) ?></td>
                                    <td><?= h($file->created) ?></td>
                                    <td><?= h($file->description) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <p><?= $this->Paginator->counter() ?></p>
                    <nav>
                        <ul class="pagination pagination-sm">
                            <?= $this->Paginator->prev('Prev', array( 'class' => '', 'tag' => 'li' ), null, array( 'class' => 'disabled', 'tag' => 'li' )) ?>
                            <?= $this->Paginator->numbers(array( 'tag' => 'li', 'separator' => '', 'currentClass' => 'active', 'currentTag' => 'a' )) ?>
                            <?= $this->Paginator->next('Next', array( 'class' => '', 'tag' => 'li' ), null, array( 'class' => 'disabled', 'tag' => 'li' )) ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
</div>
