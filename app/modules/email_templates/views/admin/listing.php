<?php if(empty($posts)){ ?>
  <div class="m-portlet__body">
    <div class="m-alert m-alert--icon m-alert--icon-solid m-alert--outline alert alert-brand alert-dismissible fade show" role="alert">
     <!--  <div class="m-alert__icon">
        <i class="flaticon-exclamation-1"></i>
        <span></span>
      </div> -->
      <div class="m-alert__text">
       <!--  <strong>
          Heads up!
        </strong> -->
        There are no Email Templates created
      </div>
      <!--
        <div class="m-alert__close">
          <button type="button" class="close" data-dismiss="alert" aria-label="Close"></button>
        </div>
      -->
    </div>
  </div>
<?php }else{ ?>

  <div class="kt-portlet">
  <div class="kt-portlet__head">
    <div class="kt-portlet__head-label">
      <h3 class="kt-portlet__head-title">
        {metronic:template:title}
      </h3>
    </div>
  </div>
  <div class="kt-portlet__body">
    <table class="table table-sm table-head-bg-brand">
      <thead>
        <tr>
          <th>
              #
          </th>
          <th width="30%">
             Title
          </th>
          <th width="30%">
              Slug
          </th>
          <th>
              Actions
          </th>
        </tr>
      </thead>
      <tbody>
        <?php
          $i = $this->uri->segment(5, 0);
          foreach($posts as $post):
        ?>
          <tr>
            <td><?php echo $i+1;?>.</td>
            <td><?php echo $post->title;?></td>
            <td><?php echo $post->slug;?></td>
            <td >
                <a href="<?php echo site_url('admin/email_templates/edit/'.$post->id);?>" class="btn m-btn--pill m-btn--air btn-primary btn-sm">
                    <i class="fa fa-edit"></i> Edit
                </a>
                <a href="<?php echo site_url('admin/email_templates/delete/'.$post->id);?>" class="btn m-btn--pill m-btn--air btn-danger btn-sm confirm" data-toggle="confirmation" data-placement="left">
                    <i class="fa fa-trash"></i> Delete
                </a>
            </td>
          </tr>
        <?php
          $i++;
          endforeach;
        ?>
      </tbody>
    </table>
  </div>
</div>
<?php } ?>
