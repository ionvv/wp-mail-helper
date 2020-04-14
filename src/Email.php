<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace WpMailHelper;

defined('ABSPATH') or die('No script kiddies please!');

use WpMailHelper\Controllers\EmailNotification;

/**
 * Class Email
 *
 * @package ionvv/wp-mail-helper
 * @since 1.0.0
 */
class Email extends EmailNotification
{
    /**
     * Function to send emails
     *
     * @method sendEmail
     * @since
     * @param mixed $to - Eemail recipient(s). Accepts string (single email address) or array(multiple email addresses).
     * @param string $from - Sender email address. Can be simple email address (john.doe@domain.tld) or Full Name <john.doe@domain.tld>
     * @param string $subject - Email subject.
     * @param string $message - Email content. Accepts the following values: post id | template path | text. See the $message_type param definition
     * @param string $message_type - Defines the email type. Defaul = 'content'. Accepts 'template' and $message should be path to template. Also accepts 'post' and in this case the $message value should be the post id ($post->ID).
     * @param array $headers - Email headers. For example: ['Content-Type: ' . 'text/html;charset=utf-8']
     * @param mixed $cc - Email CC address(es). Accepts single email address as string or multiple addresses as an array.
     * @param mixed $bcc - Email BCC address(es). Accepts single email address as string or multiple addresses as an array.
     * @param array $attachments - Email attachments. The filenames have to be filesystem paths. For example: [WP_CONTENT_DIR . '/uploads/file_to_attach.zip']
     * @return bool - email sent status
     */
    public function sendEmail(
        $to,
        string $from,
        string $subject,
        string $message,
        $message_type = 'content',
        array $headers = [],
        $cc = '',
        $bcc = '',
        $attachments = []
    ) {
        // check if function is called in WordPress. Stop if not.
        if (!defined('ABSPATH')) {
            throw new \Exception('This method should be called in WordPress.');
            return;
        }

        $this->setTo($to);
        $this->setFrom($from);
        $this->setCc($cc);
        $this->setBcc($bcc);
        $this->setSubject($subject);
        $this->setContent($message, $message_type);
        $this->setHeaders($headers);
        $this->setAttachments($attachments);

        if (empty($this->to)) {
            throw new \Exception('Please set the TO email address.');
        }
        if (empty($this->subject)) {
            throw new \Exception('Please set the email subject.');
        }
        if (empty($this->content)) {
            throw new \Exception('Please set the email content.');
        }

        foreach ($this->to as $key => $recipient_email_address) {
            // email subject
            $subject = $this->replaceEmailVariables($this->subject, $this->subject_vars);
            $subject = apply_filters('wpmh_email_subject', $subject);
            // email content
            $content = $this->replaceEmailVariables($this->content, $this->content_vars);
            $content = apply_filters('wpmh_email_content', $content);
            // email from
            $from = $this->from;
            $from = apply_filters('wpmh_email_from', $from);
            // email headers
            $headers = $this->headers;
            $headers = apply_filters('wpmh_email_headers', $headers);

            do_action('wpmh_before_sending_email', [
                'to' => $recipient_email_address,
                'subject' => $subject,
                'content' => $content,
                'headers' => $headers,
                'attachments' => $attachments
            ]);

            $status = wp_mail(
                $recipient_email_address,
                $subject,
                $content,
                $headers,
                $attachments
            );

            do_action('wpmh_after_sending_email', $status, [
                'to' => $recipient_email_address,
                'subject' => $subject,
                'content' => $content,
                'headers' => $headers,
                'attachments' => $attachments
            ]);
        }
    }

    /**
     * Function to set variables that will be replaced in the email content
     *
     * @method setVars
     * @since 1.0.0
     * @param array $content_vars - Array with key => value pairs of shortcodes that will be replaced in email body. For example ['EMAIL_ADDRESS' => 'john.doe@domain.tld']
     * @param array $subject_vars - Array with key => value pairs of shortcodes that will be replaced in email content. For example ['FULL_NAME' => 'John Doe']
     */
    public function setVars(array $content_vars = [], array $subject_vars = [])
    {
        if (!empty($content_vars)) {
            $this->content_vars = $content_vars;
        }
        if (!empty($subject_vars)) {
            $this->subject_vars = $subject_vars;
        }
    }

    /**
     * Function to test package functionality.
     *
     * @method sendTestEmail
     * @since
     * @param  [type]        $to [description]
     * @return [type]            [description]
     */
    public function sendTestEmail($to)
    {
        $this->setVars(
            [
    			'EMAIL_ADDRESS' => $to
    		],
    		[
    			'EMAIL_ADDRESS' => $to
    		]
        );

        $this->sendEmail(
	        $to, // $to
	        $to, // $from
	        'Test email for {{EMAIL_ADDRESS}}', // $subject
	        trailingslashit(dirname(__FILE__)) . 'Views/email-boilerplate.html', // $message
			'template' // $message_type
	    );
    }
}
