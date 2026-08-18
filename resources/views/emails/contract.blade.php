<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Contrato Omnivisión</title>
</head>
<body style="font-family: Arial, Helvetica, sans-serif; background: #f3f4f6; padding: 24px;">
    <div style="max-width: 560px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
        <div style="background: #1e3a8a; padding: 20px 24px;">
            <h1 style="margin: 0; color: #ffffff; font-size: 18px;">Omnivisión · Contrato de Servicio</h1>
        </div>
        <div style="padding: 24px;">
            <p style="margin: 0 0 12px; color: #374151; font-size: 14px; line-height: 1.6;">
                Hola <strong>{{ $contract->client?->name }}</strong>, adjuntamos tu contrato
                <strong>{{ $contract->contract_digital_code }}</strong>.
            </p>
            <p style="margin: 0 0 20px; color: #374151; font-size: 14px; line-height: 1.6;">
                Tu servicio de <strong>{{ $contract->serviceTypeName() }}</strong> ya está registrado.
                Cualquier consulta, respondé este correo o contactanos por nuestros canales oficiales.
            </p>
            <p style="margin: 0; color: #6b7280; font-size: 12px;">
                Este es un correo automático de Omnivisión. No respondas a este mensaje.
            </p>
        </div>
    </div>
</body>
</html>
