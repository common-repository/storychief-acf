<div class="wrap">
    <h1>Storychief ACF settings</h1>

    <p><?php esc_html_e('Please provide the correct mapping of your custom fields below.', 'storychief-acf'); ?></p>

    <form action="<?php echo esc_url(Storychief_ACF_Admin::get_page_url()); ?>" method="post">
        <input type="hidden" name="_action"
               value="save-acf-mapping"><?php wp_nonce_field(Storychief_ACF_Admin::NONCE); ?>
        <table class="form-table" cellspacing="0">
            <thead>
            <tr>
                <th><?php _e('Source: Story Chief', 'storychief-acf'); ?></th>
                <th><?php _e('Source: Wordpress', 'storychief-acf'); ?></th>
            </tr>
            </thead>
            <tbody>
			<?php if ($cf_definitions): ?>
				<?php foreach ($cf_definitions as $definition): ?>
                    <tr>
                        <th scope="row">
                            <label for="<?php echo $definition['name']; ?>"><?php echo $definition['label']; ?>
                                (<?php echo $definition['name']; ?>)</label>
                            <p class="description">Type: <?php echo $definition['type']; ?></p>
                        </th>
                        <td scope="row">
                            <select name="<?php echo $definition['name']; ?>">
								<?php foreach ($acf_fields as $group): ?>
                                    <option value=""><?php _e('Select a field', 'storychief-acf'); ?></option>
                                    <optgroup label="<?php echo $group['title']; ?>">
										<?php foreach ($group['fields'] as $field): ?>
											<?php $selected = (isset($cf_mapping[$definition['name']]) && $cf_mapping[$definition['name']] === $field['key']); ?>
                                            <option value="<?php echo $field['key']; ?>" <?php echo $selected?'selected':'' ?>>
												<?php echo $field['label']; ?>
                                            </option>
										<?php endforeach; ?>
                                    </optgroup>
								<?php endforeach; ?>
                            </select>
                        </td>
                    </tr>
				<?php endforeach; ?>
			<?php else: ?>
                <p><?php esc_html_e('Please make sure you have custom fields in your account and have revisited your channel\'s configuration page on Story Chief', 'storychief-acf'); ?></p>
			<?php endif; ?>
            </tbody>
        </table>


        <p class="submit">
            <button type="submit" id="submit" class="button button-primary">
				<?php esc_attr_e('Save changes', 'storychief-acf'); ?>
            </button>
        </p>
    </form>
</div>