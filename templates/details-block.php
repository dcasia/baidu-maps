<?php
/**
 * Template block for map details metabox
 */
?>

<p><?php echo $meta_box_description; ?></p>


<table class='form-table'>

  <?php foreach ( $baidu_meta_maps_details as $field ) : ?>
    <?php $meta = get_post_meta( $post->ID, $field['id'], true ); ?>

    <tr>
      <th><label for="<?php echo $field['id']; ?>"><?php echo $field['label']; ?></label></th>
      <td>
        <?php switch ( $field['type'] ) : ?>
          <?php case 'text': ?>

            <input type='text' name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>" value="<?php echo $meta; ?>" size='10'>
            <br>

            <?php if ( isset( $field['description'] ) ) : ?>
              <span class='description'><?php echo $field['description']; ?></span>
            <?php endif; ?>

          <?php break; ?>

          <?php case 'checkbox': ?>
            <?php $checked = $meta ? "checked='checked'" : ""; ?>
            <input type='checkbox' name="<?php echo $field['id']; ?>" id="<?php echo $field['id']; ?>"" . $checked . "/>
            <label for="<?php echo $field['id']; ?>"><?php echo $field['desc']; ?></label>
          <?php break; ?>

        <?php endswitch; ?>
      </td>
    </tr>

  <?php endforeach; ?>

</table>