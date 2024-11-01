<?php
$wpdb->query(
            $wpdb->prepare(
                'DELETE FROM '.self::$table['link'].' WHERE link_id=%d',
                [sanitize_text_field((int)@$_REQUEST['link_id'])]
            )
        );
$wpdb->query(
            $wpdb->prepare(
                'DELETE FROM '.self::$table['link'].' WHERE parent_id=%d',
                [sanitize_text_field((int)@$_REQUEST['link_id'])]
            )
        );

$wpdb->query('DELETE FROM '.self::$table['click'].' WHERE link_id > 0 AND link_id NOT IN (SELECT link_id FROM '.self::$table['link'].')');
$wpdb->query('DELETE FROM '.self::$table['click'].' WHERE parent_id > 0 AND parent_id NOT IN (SELECT link_id FROM '.self::$table['link'].')');

header('location: '.$base_url);
exit();
