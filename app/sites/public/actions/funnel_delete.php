<?php
$wpdb->query(
            $wpdb->prepare(
                'DELETE FROM '.self::$table['funnel'].' WHERE funnel_id=%d',
                [sanitize_text_field((int)@$_REQUEST['funnel_id'])]
            )
        );
$wpdb->query(
            $wpdb->prepare(
                'DELETE FROM '.self::$table['funnel_link_new'].' WHERE funnel_id=%d',
                [sanitize_text_field((int)@$_REQUEST['funnel_id'])]
            )
        );

header('location: '.$base_url.'action=funnel_grid');
exit();
