<?php
/**
 * HTML email templates for the Free Panel Assessment form.
 * Inline styles + table layout throughout — required for consistent
 * rendering across email clients (Outlook, Gmail, Apple Mail, etc).
 */

function bpx_email_shell(string $eyebrow, string $title, string $bodyHtml): string
{
    $navy      = '#0f2b52';
    $navyDeep  = '#0a1d3a';
    $gold      = '#eeb226';
    $ink       = '#16283f';
    $gray      = '#5c6b80';
    $line      = '#dfe5ee';
    $bgSoft    = '#eef1f7';

    return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{$title}</title>
</head>
<body style="margin:0;padding:0;background:{$bgSoft};font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:{$bgSoft};padding:32px 16px;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:12px;overflow:hidden;border:1px solid {$line};">

          <!-- header -->
          <tr>
            <td style="background:linear-gradient(160deg,{$navyDeep},{$navy});background-color:{$navyDeep};padding:28px 32px;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <img src="cid:bpx-logo" width="176" alt="BioPathogenix" style="display:block;width:176px;height:auto;border:0;">
                  </td>
                </tr>
              </table>
              <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:18px;">
                <tr>
                  <td style="width:22px;height:2px;background:{$gold};font-size:0;line-height:0;">&nbsp;</td>
                  <td style="padding-left:10px;font-family:'Courier New',Courier,monospace;font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#8fb4ec;">
                    {$eyebrow}
                  </td>
                </tr>
              </table>
              <div style="margin-top:10px;font-size:21px;font-weight:800;color:#ffffff;line-height:1.3;">
                {$title}
              </div>
            </td>
          </tr>

          <!-- body -->
          <tr>
            <td style="padding:32px;">
              {$bodyHtml}
            </td>
          </tr>

          <!-- footer -->
          <tr>
            <td style="padding:22px 32px;background:{$bgSoft};border-top:1px solid {$line};">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td style="font-size:13px;color:{$ink};font-weight:700;">BioPathogenix</td>
                </tr>
                <tr>
                  <td style="font-size:12.5px;color:{$gray};padding-top:4px;line-height:1.6;">
                    3004 Park Central Avenue, Nicholasville, KY 40356<br>
                    (859) 444-5660 &middot; Order@BioPathogenix.com
                  </td>
                </tr>
                <tr>
                  <td style="font-size:11.5px;color:#93a0b3;padding-top:14px;font-style:italic;">
                    For Research Use Only. Not for use in diagnostic procedures.
                  </td>
                </tr>
              </table>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;
}

function bpx_field_row(string $label, string $value): string
{
    $line = '#dfe5ee';
    $gray = '#8592a6';
    $navy = '#0f2b52';
    $safeValue = nl2br($value) !== '' ? nl2br($value) : '<span style="color:#b7bfcc;">&mdash;</span>';
    return <<<HTML
      <tr>
        <td style="padding:13px 0;border-bottom:1px solid {$line};" valign="top">
          <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
            <tr>
              <td width="150" valign="top" style="font-family:'Courier New',Courier,monospace;font-size:11.5px;color:{$gray};letter-spacing:.04em;text-transform:uppercase;padding-right:12px;">
                {$label}
              </td>
              <td valign="top" style="font-size:14.5px;color:{$navy};font-weight:700;line-height:1.5;">
                {$safeValue}
              </td>
            </tr>
          </table>
        </td>
      </tr>
HTML;
}

/**
 * @param array $f Sanitized, HTML-escaped form fields.
 */
function bpx_notification_email(array $f): array
{
    $rows =
        bpx_field_row('Full Name', $f['name']) .
        bpx_field_row('Work Email', $f['email']) .
        bpx_field_row('Organization', $f['org']) .
        bpx_field_row('Phone', $f['phone']) .
        bpx_field_row('Area of Interest', $f['interest']) .
        bpx_field_row('Targets / Sample Matrix / Needs', $f['message']);

    $body = <<<HTML
        <p style="margin:0 0 20px;font-size:14.5px;color:#5c6b80;line-height:1.6;">
          A new Free Panel Assessment request came in through the BioPathogenix website.
          Reply-to is already set to the submitter's email, so you can respond directly.
        </p>
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
          {$rows}
        </table>
        <table role="presentation" cellpadding="0" cellspacing="0" style="margin-top:26px;">
          <tr>
            <td style="background:#eeb226;border-radius:8px;">
              <a href="mailto:{$f['email']}" style="display:block;padding:13px 22px;font-size:14px;font-weight:700;color:#0a1d3a;text-decoration:none;">
                Reply to {$f['name']} &rarr;
              </a>
            </td>
          </tr>
        </table>
HTML;

    $subject = 'New Panel Assessment Request — ' . $f['name'];
    $html = bpx_email_shell('Free Panel Assessment', 'New request from ' . $f['name'], $body);

    $text = "New Free Panel Assessment request\n\n" .
        "Full Name: {$f['name']}\n" .
        "Work Email: {$f['email']}\n" .
        "Organization: {$f['org']}\n" .
        "Phone: {$f['phone']}\n" .
        "Area of Interest: {$f['interest']}\n" .
        "Targets / Sample Matrix / Needs:\n{$f['message']}\n";

    return ['subject' => $subject, 'html' => $html, 'text' => $text];
}

function bpx_confirmation_email(string $name): array
{
    $trimmedName = trim($name);
    $safeName = $trimmedName !== '' ? htmlspecialchars($trimmedName, ENT_QUOTES, 'UTF-8') : 'there';

    $body = <<<HTML
        <p style="margin:0 0 16px;font-size:15px;color:#16283f;line-height:1.6;">
          Hi {$safeName},
        </p>
        <p style="margin:0 0 16px;font-size:14.5px;color:#5c6b80;line-height:1.65;">
          Thanks for reaching out to BioPathogenix. We've received your Free Panel Assessment
          request and a scientist will follow up with the right configuration and pricing
          &mdash; usually within one business day.
        </p>
        <p style="margin:0;font-size:14.5px;color:#5c6b80;line-height:1.65;">
          In the meantime, if you'd like to add detail or have an urgent question, just reply
          to this email or call us at (859) 444-5660.
        </p>
HTML;

    $subject = "We've received your BioPathogenix panel assessment request";
    $html = bpx_email_shell('Request Received', 'Thanks for reaching out.', $body);

    $text = "Hi {$trimmedName},\n\n" .
        "Thanks for reaching out to BioPathogenix. We've received your Free Panel Assessment " .
        "request and a scientist will follow up with the right configuration and pricing " .
        "usually within one business day.\n\n" .
        "Reply to this email or call (859) 444-5660 with any urgent questions.\n";

    return ['subject' => $subject, 'html' => $html, 'text' => $text];
}
