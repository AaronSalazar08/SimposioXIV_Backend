<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Código de verificación – XIV Simposio</title>
</head>
<body style="margin:0;padding:0;background-color:#f0f4f8;font-family:Arial,Helvetica,sans-serif;color:#1a1a2e;">

  <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f4f8;padding:32px 16px;">
    <tr>
      <td align="center">
        <table width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

          {{-- Header --}}
          <tr>
            <td style="background-color:#005DA4;border-radius:12px 12px 0 0;padding:32px 40px;text-align:center;">
              <p style="margin:0 0 6px 0;font-size:11px;font-weight:600;letter-spacing:2px;text-transform:uppercase;color:#93c5e8;">
                Universidad de Costa Rica
              </p>
              <h1 style="margin:0;font-size:22px;font-weight:700;color:#ffffff;line-height:1.3;">
                XIV Simposio de Informática Empresarial
              </h1>
              <p style="margin:8px 0 0 0;font-size:13px;color:#bfdbf7;">
                Escuela de Ciencias de la Computación e Informática
              </p>
            </td>
          </tr>

          {{-- Body --}}
          <tr>
            <td style="background-color:#ffffff;padding:36px 40px;">

              <p style="margin:0 0 16px 0;font-size:16px;font-weight:600;color:#005DA4;">
                Hola, {{ $usuario->nombre }}
              </p>

              <p style="margin:0 0 24px 0;font-size:14px;line-height:1.6;color:#374151;">
                Recibiste este correo porque solicitaste cambiar tu contraseña en el sistema del Simposio.
                Usá el siguiente código para verificar tu identidad:
              </p>

              {{-- OTP code box --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 24px 0;">
                <tr>
                  <td align="center">
                    <table cellpadding="0" cellspacing="0">
                      <tr>
                        <td style="background-color:#f0f7ff;border:2px solid #21BBEF;border-radius:12px;padding:24px 40px;text-align:center;">
                          <p style="margin:0 0 8px 0;font-size:11px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:#005DA4;">
                            Tu código de verificación
                          </p>
                          <p style="margin:0;font-size:40px;font-weight:700;letter-spacing:14px;color:#003A6E;font-family:'Courier New',Courier,monospace;line-height:1.1;">
                            {{ $codigo }}
          </p>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              {{-- Expiry warning --}}
              <p style="margin:0 0 20px 0;font-size:13px;line-height:1.5;color:#92400e;background-color:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:12px 16px;">
                <strong>⏱ Expira en 2 minutos.</strong> Si no lo usás a tiempo, deberás solicitar un nuevo código desde la aplicación.
              </p>

              <p style="margin:0 0 16px 0;font-size:13px;line-height:1.6;color:#6b7280;">
                Si no solicitaste este cambio, podés ignorar este correo. Tu contraseña actual permanece sin cambios.
              </p>

              <p style="margin:0;font-size:13px;line-height:1.6;color:#374151;">
                ¡Hasta pronto!
              </p>

            </td>
          </tr>

          {{-- Footer --}}
          <tr>
            <td style="background-color:#003A6E;border-radius:0 0 12px 12px;padding:20px 40px;text-align:center;">
              <p style="margin:0 0 4px 0;font-size:12px;color:#93c5e8;font-weight:600;">
                XIV Simposio de Informática Empresarial
              </p>
              <p style="margin:0;font-size:11px;color:#7aadcc;">
                Escuela de Ciencias de la Computación e Informática – Universidad de Costa Rica
              </p>
              <p style="margin:8px 0 0 0;font-size:11px;color:#5a8fa8;">
                Este correo fue generado automáticamente. No lo reenvíes a terceros.
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>

</body>
</html>
