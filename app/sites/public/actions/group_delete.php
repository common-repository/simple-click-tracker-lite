<?php
$wpdb->query(
            $wpdb->prepare(
                'DELETE FROM '.self::$table['group'].' WHERE group_id=%d',
                [sanitize_text_field((int)@$_REQUEST['group_id'])]
            )
        );
 $wpdb->query(
                    $wpdb->prepare(
                        "UPDATE ".self::$table['link']." set group_id=%d where group_id=%d",
                        '0',sanitize_text_field((int)@$_REQUEST['group_id'])
                    )
                );
header('location: '.$base_url.'action=group_grid');
exit();