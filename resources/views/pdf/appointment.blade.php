<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Cita #{{ $appointment->id }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 30px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 3px solid #0d6efd;
        }
        .header h1 {
            color: #0d6efd;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header p {
            color: #666;
            font-size: 11px;
        }
        .section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            background-color: #0d6efd;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
            border-radius: 3px;
        }
        .info-grid {
            display: table;
            width: 100%;
            border-collapse: collapse;
        }
        .info-row {
            display: table-row;
        }
        .info-label {
            display: table-cell;
            font-weight: bold;
            padding: 8px 12px;
            background-color: #f8f9fa;
            width: 35%;
            border: 1px solid #dee2e6;
        }
        .info-value {
            display: table-cell;
            padding: 8px 12px;
            border: 1px solid #dee2e6;
        }
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success {
            background-color: #198754;
            color: white;
        }
        .badge-primary {
            background-color: #0d6efd;
            color: white;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        .team-list {
            list-style: none;
            padding: 0;
        }
        .team-list li {
            padding: 4px 0;
        }
        .footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #dee2e6;
            text-align: center;
            color: #666;
            font-size: 10px;
        }
        .highlight {
            background-color: #fff3cd;
            padding: 2px 6px;
            border-radius: 3px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Cita #{{ $appointment->id }}</h1>
        <p>Generado el {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <!-- Información General -->
    <div class="section">
        <div class="section-title">Información General de la Cita</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">ID de Cita</div>
                <div class="info-value">#{{ $appointment->id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Tipo de Cita</div>
                <div class="info-value">
                    <span class="badge badge-primary">{{ ucfirst($appointment->appointment_type) }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Estado</div>
                <div class="info-value">
                    <span class="badge badge-success">{{ ucfirst($appointment->status) }}</span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha Solicitada</div>
                <div class="info-value">{{ $appointment->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha Programada (Cotización)</div>
                <div class="info-value">{{ $appointment->scheduled_date->format('d/m/Y H:i:s') }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Duración</div>
                <div class="info-value">{{ $appointment->duration_minutes }} minutos</div>
            </div>
            <div class="info-row">
                <div class="info-label">Dirección</div>
                <div class="info-value">{{ $appointment->address }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Descripción</div>
                <div class="info-value">{{ $appointment->description ?: 'Sin descripción' }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Ejecución</div>
                <div class="info-value">
                    <span class="highlight">{{ $appointment->updated_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Información del Cliente -->
    <div class="section">
        <div class="section-title">Información del Cliente</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre</div>
                <div class="info-value">{{ $appointment->client->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Email</div>
                <div class="info-value">{{ $appointment->client->email }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Teléfono</div>
                <div class="info-value">{{ $appointment->client->phone ?? 'No especificado' }}</div>
            </div>
        </div>
    </div>

    <!-- Información del Equipo -->
    @if($appointment->team)
    <div class="section">
        <div class="section-title">Equipo Asignado</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Nombre del Equipo</div>
                <div class="info-value">{{ $appointment->team->name }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Líder del Equipo</div>
                <div class="info-value">
                    {{ $appointment->team->leader->name ?? 'Sin líder asignado' }}
                    @if($appointment->team->leader)
                        ({{ $appointment->team->leader->email }})
                    @endif
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Miembros del Equipo</div>
                <div class="info-value">
                    @if($appointment->team->members->count() > 0)
                        <ul class="team-list">
                            @foreach($appointment->team->members as $member)
                                <li>• {{ $member->name }} ({{ $member->email }})</li>
                            @endforeach
                        </ul>
                    @else
                        Sin miembros asignados
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Información de la Cotización -->
    @if($appointment->quotation)
    <div class="section">
        <div class="section-title">Detalles de la Cotización</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Materiales</div>
                <div class="info-value">{{ $appointment->quotation->materials }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Horas de Trabajo</div>
                <div class="info-value">{{ $appointment->quotation->labor_hours }} horas</div>
            </div>
            <div class="info-row">
                <div class="info-label">Personal Requerido</div>
                <div class="info-value">{{ $appointment->quotation->required_staff }} personas</div>
            </div>
            <div class="info-row">
                <div class="info-label">Precio Total</div>
                <div class="info-value">
                    <strong style="font-size: 14px; color: #198754;">
                        ${{ number_format($appointment->quotation->price, 2) }} COP
                    </strong>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Fecha de Creación de Cotización</div>
                <div class="info-value">{{ $appointment->quotation->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            @if($appointment->quotation->approved_at)
            <div class="info-row">
                <div class="info-label">Fecha de Aprobación</div>
                <div class="info-value">
                    <span class="highlight">{{ $appointment->quotation->approved_at->format('d/m/Y H:i:s') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <!-- Cronología de Eventos -->
    <div class="section">
        <div class="section-title">Cronología de Eventos</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">1. Cita Solicitada</div>
                <div class="info-value">{{ $appointment->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            @if($appointment->quotation)
            <div class="info-row">
                <div class="info-label">2. Cotización Creada</div>
                <div class="info-value">{{ $appointment->quotation->created_at->format('d/m/Y H:i:s') }}</div>
            </div>
            @if($appointment->quotation->approved_at)
            <div class="info-row">
                <div class="info-label">3. Cotización Aprobada</div>
                <div class="info-value">{{ $appointment->quotation->approved_at->format('d/m/Y H:i:s') }}</div>
            </div>
            @endif
            @endif
            <div class="info-row">
                <div class="info-label">4. Cita Ejecutada</div>
                <div class="info-value">
                    <strong>{{ $appointment->updated_at->format('d/m/Y H:i:s') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Sistema de Gestión de Citas de Telecomunicaciones</p>
        <p>Este documento es generado automáticamente y contiene información confidencial</p>
    </div>
</body>
</html>
