
<div class="kt-portlet">
  <div class="kt-portlet__head">
    <div class="kt-portlet__head-label">
      <h3 class="kt-portlet__head-title">
        {metronic:template:title}
      </h3>
    </div>
  </div>
  <!--begin::Form-->
  <?php echo form_open_multipart(current_url(),'class="kt-form" role="form"');?>
    <div class="kt-portlet__body">
      <div class="form-group m-form__group">
        <label for="">
          Email Template Name
        </label>
        <?php echo form_input('title',$post->title,'class="form-control m-input m-input--air" placeholder="Email Template Name"');?>
        <span class="m-form__help">

        </span>
      </div>
      <div class="form-group m-form__group">
        <label for="">
          Description
        </label>
        <?php echo form_input('description',$post->description,'class="form-control m-input m-input--air" placeholder="Description"');?>
        <span class="m-form__help">

        </span>
      </div>
      <div class="form-group m-form__group">
        <label for="">
          Content
        </label>
        <?php
          echo form_textarea(
            array(
              'name' => 'content',
              'value' => $post->content,
              'class' => 'summernote',
            )
          );
        ?>
        <span class="m-form__help">

        </span>
      </div>
    </div>
    <div class="kt-portlet__foot">
      <div class="kt-form__actions">
        <button type="submit" class="btn btn-primary submit-button">Save</button>
        <button type="submit" disabled="disabled" class="btn btn-primary processing-button"><i class="fas fa-circle-notch fa-spin"></i> Processing</button>
      </div>
    </div>
  <?php echo form_close(); ?>
  <!--end::Form-->
</div>

<script type="text/javascript">
  $(document).ready(function(){
    "use strict";
    // Class definition
    var KTSummernoteDemo = function () {    
        // Private functions
        var demos = function () {
            $('.summernote').summernote({
                height: 250
            });
        }

        return {
            // public functions
            init: function() {
                demos(); 
            }
        };
    }();

    // Initialization
    jQuery(document).ready(function() {
        KTSummernoteDemo.init();
    });
});
</script>
