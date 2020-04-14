<?php # -*- coding: utf-8 -*-

declare(strict_types=1);

namespace WpMailHelper\Controllers;

defined('ABSPATH') or die('No script kiddies please!');

/**
* Class EmailNotification
*
* @package ionvv/wp-mail-helper
* @since 1.0.0
*/
class EmailNotification
{
    /**
     * Receiver(s) email address
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $to = [];

    /**
     * Sender email address
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $from;

    /**
     * CC email address(es)
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $cc = [];

    /**
     * BCC email address(es)
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $bcc = [];

    /**
     * Email subject
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $subject;

    /**
     * Email content
     *
     * @since 1.0.0
     * @access protected
     * @var string
     */
    protected $content;

    /**
     * Email headers
     * Example: ['Content-Type: ' . 'text/html;charset=utf-8']
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $headers = [];

    /**
     * Emails attachment(s)
     * The filenames have to be filesystem paths.
     * For example: [WP_CONTENT_DIR . '/uploads/file_to_attach.zip']
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $attachments = [];

    /**
     * Variables that should be replaces in the email subject
     * Array with key => value pairs of shortcodes that will be replaced in email body.
     * For example ['EMAIL_ADDRESS' => 'john.doe@domain.tld']
     * In content, the shortcode {{EMAIL_ADDRESS}} will be replaced with 'john.doe@domain.tld'
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $subject_vars = [];

    /**
     * Variables that should be replaces in the email content
     * Array with key => value pairs of shortcodes that will be replaced in email body.
     * For example ['EMAIL_ADDRESS' => 'john.doe@domain.tld']
     * In content, the shortcode {{EMAIL_ADDRESS}} will be replaced with 'john.doe@domain.tld'
     *
     * @since 1.0.0
     * @access protected
     * @var array
     */
    protected $content_vars = [];

	/**
	 * Process email content
	 * Used when a post is used as email content
	 *
	 * @method processEmailContent
	 * @since 1.0.0
	 * @static
	 * @param string $email_content_raw
	 * @return string $content
	 */
	protected static function processEmailContent($email_content_raw = ''): string
	{
		$content = convert_chars(convert_smilies(wptexturize($email_content_raw)));
		if (!empty($GLOBALS['wp_embed'])) {
			$content = $GLOBALS['wp_embed']->autoembed($content);
		}
		$content = wpautop($content);
		$content = do_shortcode(shortcode_unautop($content));

		return $content;
	}

    /**
     * Function to replace email variables
     * Shortcodes can be used in the email content. For example, {{EMAIL_ADDRESS}}
     * $vars should be an associative array like follows:
     * ['EMAIL_ADDRESS' => 'john.doe@domain.tld']
     * In content, the following will be replaced:
     * Hello, {{EMAIL_ADDRESS}}
     * to
     * Hello, john.doe@domain.tld
     *
     * @method replaceEmailVariables
     * @since 1.0.0
     * @static
     * @param  string $content
     * @param array $vars
     * @return string $content
     */
    protected static function replaceEmailVariables(string $content, array $vars): string
    {
        if (!empty($vars)) {
            foreach ($vars as $shortcode_key => $shortcode_value) {
                $content = str_replace(
                    self::getShortcodeFromVariableHandle($shortcode_key),
                    $shortcode_value,
                    $content
                );
            }
        }

        return $content;
    }

    /**
     * Create shortcode from the variable
     *
     * @method getShortcodeFromVariableHandle
     * @since 1.0.0
     * @param string $shortcode_key
     * @return string
     */
    protected static function getShortcodeFromVariableHandle($shortcode_key): string
    {
        return '{{' . $shortcode_key . '}}';
    }

    /**
     * Set the receiver email address in current instance
     *
     * @method setTo
     * @since 1.0.0
     * @param mixed $to - can be string or array
     */
    protected function setTo($to)
    {
        if (is_array($to)) {
            $this->to = $to;
        } else {
            $this->to[] = $to;
        }
    }

    /**
     * Set the sender email address in current instance
     * Set the From, Reply-To and Return-Path headers in current instance
     *
     * @method setFrom
     * @since 1.0.0
     * @param string $from
     */
    protected function setFrom(string $from)
    {
        $this->from = $from;
        if (!empty($this->from)) {
            $this->headers[] = 'From: ' . $this->from;
            $this->headers[] = 'Reply-To: ' . $this->from;
            $this->headers[] = 'Return-Path: ' . $this->from;
        }
    }

    /**
     * Set the email subject in current instance
     *
     * @method setSubject
     * @since 1.0.0
     * @param string $subject
     */
    protected function setSubject(string $subject)
    {
        $this->subject = $subject;
    }

    /**
     * Set the email content in current instance
     * and process it (run do_shortcode and other functions)
     *
     * @method setContent
     * @since 1.0.0
     * @param string $content - Email content. Can be string, path to template or post id (see $content_type)
     * @param string $content_type - Email content type. Default set to 'content'
     *                              which means $content will be parsed as string.
     *                              When set to 'template', $content should be a path to template file.
     *                              When set to 'post', $content should be post id (int value).
     */
    protected function setContent(string $content, string $content_type)
    {
        switch ($content_type) {
            case 'template':
                if (file_exists($content)) {
                    ob_start();
                    include $content;
                    $this->content = $this->processEmailContent(ob_get_clean());
                } else {
                    throw new \Exception('Invalid template path.');
                }
                break;
            case 'post':
                if (intval($content)) {
                    $this->content = $this->processEmailContent(get_the_content(null, false, (int) $content));
                }
                break;
            case 'content':
            default:
                $this->content = $this->processEmailContent($content);
                break;
        }
    }

    /**
     * Set the email headers in current instance.
     *
     * @method setHeaders
     * @since 1.0.0
     * @param array $headers
     */
    protected function setHeaders(array $headers = [])
    {
        if (empty($headers)) {
            $headers = [
                'MIME-Version: ' . '1.0',
                'Content-Type: ' . 'text/html;charset=utf-8'
            ];
        }
        $this->headers = array_merge($headers, $this->headers);
    }

    /**
     * Set the attachments for current instance.
     * The filenames have to be filesystem paths.
     * For example: [WP_CONTENT_DIR . '/uploads/file_to_attach.zip']
     *
     * @method setAttachments
     * @since 1.0.0
     * @param array $attachments - filesystem paths.
     */
    protected function setAttachments(array $attachments = [])
    {
        $this->attachments = $attachments;
    }

    /**
     * Set the CC for current instance.
     *
     * @method setCc
     * @since 1.0.0
     * @param array $cc
     */
    protected function setCc($cc = [])
    {
        if (is_array($cc)) {
            $this->cc = $cc;
        } else {
            $this->cc[] = $cc;
        }
        // add cc emails to headers
        if (!empty($this->cc)) {
            $this->headers[] = 'CC: ' . implode(',', $this->cc);
        }
    }

    /**
     * Set the BCC for current instance.
     *
     * @method setBcc
     * @since 1.0.0
     * @param array $bcc
     */
    protected function setBcc($bcc = [])
    {
        if (is_array($bcc)) {
            $this->bcc = $bcc;
        } else {
            $this->bcc[] = $bcc;
        }
        // add bcc emails to headers
        if (!empty($this->bcc)) {
            $this->headers[] = 'BCC: ' . implode(',', $this->bcc);
        }
    }
}
