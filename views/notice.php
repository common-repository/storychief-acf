<?php if (isset($type)): ?>
	<?php if ($type == 'config-set') : ?>
        <div id="storychief-warning" class="updated notice">
            <p>
                <strong><?php printf(esc_html__('Configuration saved', 'storychief')); ?></strong>
            </p>
        </div>
	<?php elseif ($type == 'undefined') : ?>
        <div id="storychief-warning" class="error notice">
            <p>
                <strong><?php printf(esc_html__('An unknown error occurred', 'storychief')); ?></strong>
            </p>
        </div>
	<?php endif; ?>
<?php endif; ?>
