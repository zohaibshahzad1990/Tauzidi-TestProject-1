<div class="m-portlet__body">
    <div id="sort">
        <div class="dd" id="menu_sort">
            <ol class="dd-list">
                <?php foreach($posts as $post): ?>
                    <li class="dd-item" data-id="<?php echo $post->id?>">
                        <div class="dd-handle">
                            <?php
                                echo $post->name;
                                echo $post->active==1?' - Active':' - Hidden';
                            ?>

                        </div>
                        <?php $this->menus_m->display_children($post->id); ?>
                    </li>
                <?php endforeach;?>
            </ol>
        </div>
    </div>
    <div class="row d-none ">
        <div class="col-md-12">
            <h3>Serialised Output (per list)</h3>
            <textarea id="menu_sort_output" class="form-control col-md-12 margin-bottom-10"></textarea>
        </div>
    </div>
</div>
