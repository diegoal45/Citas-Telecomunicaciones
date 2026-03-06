<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $notification->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #212529;
            background-color: #f8f9fa;
            padding: 20px 10px;
        }
        
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .header {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        
        .header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 8px 0;
            letter-spacing: -0.5px;
        }
        
        .header p {
            margin: 0;
            opacity: 0.9;
            font-size: 14px;
        }
        
        .content {
            padding: 40px 30px;
        }
        
        .greeting {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 25px;
            color: #212529;
        }
        
        .notification-badge {
            display: inline-block;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white;
            padding: 6px 16px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }
        
        .notification-title {
            font-size: 24px;
            font-weight: 700;
            color: #212529;
            margin-bottom: 16px;
            line-height: 1.3;
        }
        
        .notification-message {
            font-size: 16px;
            color: #495057;
            line-height: 1.8;
            margin-bottom: 25px;
        }
        
        .info-box {
            background: linear-gradient(to right, #f8f9fa 0%, #e9ecef 100%);
            border-left: 4px solid #0d6efd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        
        .info-box-title {
            font-size: 13px;
            font-weight: 700;
            color: #0d6efd;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #dee2e6;
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }
        
        .info-value {
            color: #6c757d;
            font-size: 14px;
            text-align: right;
        }
        
        .cta-button {
            display: inline-block;
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            color: white !important;
            padding: 14px 32px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 16px;
            margin-top: 10px;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            transition: all 0.3s ease;
        }
        
        .cta-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
        }
        
        .divider {
            height: 1px;
            background: linear-gradient(to right, transparent 0%, #dee2e6 50%, transparent 100%);
            margin: 30px 0;
        }
        
        .footer {
            background-color: #f8f9fa;
            padding: 30px;
            text-align: center;
            border-top: 1px solid #dee2e6;
        }
        
        .footer-text {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 8px;
        }
        
        .footer-link {
            color: #0d6efd;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer-link:hover {
            text-decoration: underline;
        }
        
        .footer-copyright {
            font-size: 12px;
            color: #adb5bd;
            margin-top: 15px;
        }
        
        .timestamp {
            font-size: 12px;
            color: #adb5bd;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <!-- Header -->
        <div class="header">
            <h1>📱 ExCitel</h1>
            <p>Sistema de Gestión de Citas de Telecomunicaciones</p>
        </div>
        
        <!-- Content -->
        <div class="content">
            <p class="greeting">¡Hola {{ $userName }}! 👋</p>
            
            <span class="notification-badge">{{ str_replace('_', ' ', strtoupper($notification->type)) }}</span>
            
            <div class="notification-title">
                {{ $notification->title }}
            </div>
            
            <div class="notification-message">
                {{ $notification->message }}
            </div>
            
            @if(!empty($notification->data) && is_array($notification->data) && count($notification->data) > 0)
                <div class="info-box">
                    <div class="info-box-title">📋 Detalles de la notificación</div>
                    @foreach($notification->data as $key => $value)
                        <div class="info-row">
                            <span class="info-label">{{ ucfirst(str_replace('_', ' ', $key)) }}</span>
                            <span class="info-value">{{ $value }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
            
            <div class="divider"></div>
            
            <div style="text-align: center;">
                <a href="http://localhost:8000/login" class="cta-button">
                    🚀 Ir a la Aplicación
                </a>
            </div>
            
            <p class="timestamp">
                📅 {{ $notification->created_at->locale('es')->isoFormat('DD [de] MMMM [de] YYYY [a las] HH:mm') }}
            </p>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p class="footer-text">
                Este es un correo automático generado por el sistema.
            </p>
            <p class="footer-text">
                Si tienes dudas, contacta con soporte técnico:<br>
                <a href="mailto:{{ config('mail.from.address') }}" class="footer-link">{{ config('mail.from.address') }}</a>
            </p>
            
            <p class="footer-copyright">
                &copy; {{ date('Y') }} ExCitel. Todos los derechos reservados.
            </p>
        </div>
    </div>
</body>
</html>
