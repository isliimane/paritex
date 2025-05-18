<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <title>{{ $content['subject'] }}</title>

    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600" rel="stylesheet" type="text/css">
    <!-- Web Font / @font-face : BEGIN -->

    @php $color = settingHelper('primary_color'); @endphp
    <style>
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            font-family: 'Poppins', sans-serif !important;
            font-size: 14px;
            margin-bottom: 10px;
            line-height: 24px;
            color:#8094ae;
            font-weight: 400;
        }
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
            margin: 0;
            padding: 0;
            font-family: 'Poppins', sans-serif !important;
        }
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }

        table table table {
            table-layout: auto;
        }
        a {
            text-decoration: none;
            color: {{$color}} !important;
            word-break: break-all;
        }
        img {
            -ms-interpolation-mode:bicubic;
        }
        .email-body {
            width: 96%;
            margin: 0 auto;
            background: #ffffff;
            padding: 10px !important;
        }
        .email-heading {
            font-size: 18px;
            color: {{$color}};
            font-weight: 600;
            margin: 0;
            line-height: 1.4;
        }
        .email-btn {
            background: {{$color}};
            border-radius: 4px;
            color: #ffffff !important;
            display: inline-block;
            font-size: 13px;
            font-weight: 600;
            line-height: 44px;
            text-align: center;
            text-decoration: none;
            text-transform: uppercase;
            padding: 0 30px;
        }
        .email-heading-s2 {
            font-size: 16px;
            color: {{$color}};
            font-weight: 600;
            margin: 0;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .link-block {
            display: block;
        }
        p {
            margin-top: 0;
            margin-bottom: 1rem;
        }
        .email-note {
            margin: 0;
            font-size: 13px;
            line-height: 22px;
            color: {{$color}};
        }
        .claim-header {
            background-color: #f7f9fc;
            padding: 15px 20px;
            border-left: 4px solid {{$color}};
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .claim-subject {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 0;
        }
        .response-content {
            color: #444;
            line-height: 1.6;
            margin-bottom: 20px;
            padding: 15px;
            background-color: #ffffff;
            border-radius: 4px;
        }
        .attachment-section {
            background-color: #f5f9ff;
            padding: 12px 15px;
            border-radius: 4px;
            margin-top: 10px;
            border: 1px dashed #d7e5f9;
        }
        .attachment-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .attachment-item {
            display: flex;
            align-items: center;
            padding: 8px;
            background: #ffffff;
            border-radius: 4px;
            margin-bottom: 5px;
            border: 1px solid #eaeaea;
        }
        .attachment-icon {
            margin-right: 10px;
            color: {{$color}};
        }
        .attachment-name {
            font-size: 13px;
            color: #444;
        }
        .footer-text {
            font-size: 13px;
            color: #6e7a8a;
        }
    </style>
</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f5f6fa;">
<center style="width: 100%; background-color: #f5f6fa;">
    <table width="100%" border="0" cellpadding="0" cellspacing="0" bgcolor="#f5f6fa">
        <tr>
            <td style="padding: 40px 0;">
                <table style="width:100%;max-width:620px;margin:0 auto;">
                    <tbody>
                    <tr>
                        @php
                            $logo = settingHelper('invoice_logo');
                        @endphp
                        <td style="text-align: center; padding-bottom:25px">
                            <a href="{{ url('/') }}">
                                <img src="{{($logo != [] && @is_file_exists($logo['image_118x45'])) ? static_asset($logo['image_118x45']) : static_asset('images/default/dark-logo.png') }}" alt="Logo">
                            </a>
                        </td>
                    </tr>
                    </tbody>
                </table>
                <table style="width:100%;max-width:620px;margin:0 auto;background-color:#ffffff;">
                    <tbody>
                    <tr>
                        <td style="padding: 30px 30px 20px 30px;">
                            <h1 style="font-size: 20px; color: #333333; font-weight: 600; margin-bottom: 20px;">Claim Response</h1>
                            
                            <!-- Claim Subject -->
                            <div class="claim-header">
                                <p class="claim-subject">{{ $content['subject'] }}</p>
                            </div>
                            
                            <!-- Response Message -->
                            <div class="response-content">
                                @if(gettype($content['message']) == 'array')
                                    @foreach($content['message'] as $message)
                                        <p style="text-align: justify">{{ $message }}</p>
                                    @endforeach
                                @else
                                    <p>{{ $content['message']}}</p>
                                @endif
                            </div>
                            
                            <!-- Attachment Section (if file exists) -->
                            @if(isset($content['file']) && !empty($content['file']))
                            <div class="attachment-section">
                                <p class="attachment-title">Attachment</p>
                                
                                @if(gettype($content['file']) == 'array')
                                    @foreach($content['file'] as $file)
                                        <div class="attachment-item">
                                            <span class="attachment-icon">📎</span>
                                            <span class="attachment-name">{{ $file }}</span>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="attachment-item">
                                        <span class="attachment-icon">📎</span>
                                        <span class="attachment-name">{{ $content['file'] }}</span>
                                    </div>
                                @endif
                            </div>
                            @endif
                            
                            <!-- Support Contact Info -->
                            <p style="margin-top: 25px; font-weight: 500; color: #555;">
                                If you have any questions about this response, please don't hesitate to contact our support team.
                            </p>
                        </td>
                    </tr>
                    
                    @if(!blank(settingHelper('mail_signature') || settingHelper('mail_signature') != ''))
                        <tr>
                            <td style="text-align:left;padding: 0 30px 30px">
                                {!! settingHelper('mail_signature') !!}
                            </td>
                        </tr>
                    @endif
                    </tbody>
                </table>
                <table style="width:100%;max-width:620px;margin:0 auto;">
                    <tbody>
                    <tr>
                        <td style="text-align: center; padding:25px 20px 0;">
                            <p class="footer-text">{{ settingHelper('copyright') }}</p>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>
</center>
</body>
</html>