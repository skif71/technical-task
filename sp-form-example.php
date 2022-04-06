<?php

/*
Реализовать плагин для Wordpress который будет представлять из себя систему запроса обратной связи.
Плагин должен устанавливаться через менеджер установки плагинов WP.
Плагин содержит таблицу для просмотра запросов на обратную связь с колонками:
- Name
- Email
- Phone
- Date
Вывод формы отправки заявки на обратную связь осуществляется через shortcode.
Все данные в заявке должны записываться в базу данных и выводиться в админке
*/

/*
Plugin Name: Example-form
Description: Simple Contact Form
Version: 1.0
Author: Alex Nenko
*/

function example_form()
{
    $content = '<form action="" method="post">';

    $content .= '<input type="text" name="full_name" placeholder="Your name" />';
    $content .= '<br />';

    $content .= '<input type="text" name="email_adress" placeholder="Your email" />';
    $content .= '<br />';

    $content .= '<input type="text" name="phone_number" placeholder="Your phone number" />';
    $content .= '<br />';

    $content .= '<input type="date" name="date" placeholder="Date" />';
    $content .= '<br />';

    $content .= '<input type="submit" name="example_submit_form" value="Submit information" />';

    $content .= '</form>';

    return $content;
}

add_shortcode('example_contact_form', 'example_form');

function set_html_content_type()
{
    return 'text/html';
}

function example_form_capture()
{
    global $wpdb;

    if (array_key_exists('example_submit_form', $_POST)) {
        $to = "support@example.com";
        $subj = "example site form submission";
        $data = [
            'name' => $_POST['full_name'],
            'email' => $_POST['email_adress'],
            'phone' => $_POST['phone_number'],
            'date' => $_POST['date']
        ];

        $mail_body = "Name: ".$data['name']."<br>";
        $mail_body .= "Mail: ".$data['email']."<br>";
        $mail_body .= "Phone: ".$data['phone']."<br>";
        $mail_body .= "Date: ".$data['date'];

        add_filter('wp_mail_content_type', 'set_html_content_type');
        wp_mail($to, $subj, $mail_body);
        remove_filter('wp_mail_content_type', 'set_html_content_type');
        $wpdb->insert($wpdb->prefix . "form_submissions", $data);
    }
}

add_action('wp_head', 'example_form_capture');

function example_admin_menu()
{
    add_menu_page(
        'Example Form',
        'Example Form',
        'manage_options',
        'example-form-admin-menu',
        'example_reports_page'
    );
}

function example_reports_page()
{
    global $wpdb;
    $sqlQuerys = $wpdb->get_results(" SELECT * FROM " . $wpdb->prefix . "form_submissions");

    $row_html = '';

    foreach ($sqlQuerys as $row) {
        $row_html .= '<tr>';
        $row_html .= get_columns_html($row->name);
        $row_html .= get_columns_html($row->email);
        $row_html .= get_columns_html($row->phone);
        $row_html .= get_columns_html($row->date);
        $row_html .= '</tr>';
    }


    ?>
    <table class="wp-list-table widefat fixed striped table-view-list posts">
        <thead>
        <tr>
            <td>Name</td>
            <td>Email</td>
            <td>Phone</td>
            <td>Date</td>
        </tr>
        </thead>
        <? echo $row_html ?>
    </table>

    <?php

}

add_action('admin_menu', 'example_admin_menu');

function get_columns_html($value)
{
    $result = '<td>';
    $result .= $value;
    $result .= '</td>';
    return $result;
}

?>



