<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bienvenido/a al XIV Simposio de Informática Empresarial</title>
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

              <p style="margin:0 0 20px 0;font-size:16px;font-weight:600;color:#005DA4;">
                ¡Hola, {{ $usuario->nombre }}!
              </p>

              <p style="margin:0 0 16px 0;font-size:14px;line-height:1.6;color:#374151;">
                Te damos la bienvenida al <strong>XIV Simposio de Informática Empresarial</strong> de la
                Universidad de Costa Rica. A continuación encontrás tus credenciales de acceso al sistema
                de inscripciones:
              </p>

              {{-- Credentials box --}}
              <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f0f7ff;border:1px solid #bfdbf7;border-radius:8px;margin:24px 0;">
                <tr>
                  <td style="padding:24px 28px;">
                    <p style="margin:0 0 4px 0;font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#005DA4;">
                      Tus credenciales de acceso
                    </p>

                    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top:16px;">
                      <tr>
                        <td style="padding:6px 0;border-bottom:1px solid #dbeafe;">
                          <span style="font-size:12px;color:#6b7280;display:block;margin-bottom:2px;">Correo electrónico</span>
                          <span style="font-size:14px;font-weight:600;color:#1a1a2e;">{{ $usuario->email }}</span>
                        </td>
                      </tr>
                      @if($usuario->carnet)
                      <tr>
                        <td style="padding:6px 0;border-bottom:1px solid #dbeafe;">
                          <span style="font-size:12px;color:#6b7280;display:block;margin-bottom:2px;">
                            Carnet
                            <span style="font-size:11px;color:#005DA4;">(también podés usarlo para ingresar)</span>
                          </span>
                          <span style="font-size:14px;font-weight:600;color:#1a1a2e;">{{ $usuario->carnet }}</span>
                        </td>
                      </tr>
                      @endif
                      <tr>
                        <td style="padding:6px 0;">
                          <span style="font-size:12px;color:#6b7280;display:block;margin-bottom:2px;">Contraseña</span>
                          <span style="font-size:16px;font-weight:700;color:#004A87;letter-spacing:1px;font-family:Courier New,Courier,monospace;">
                            {{ $passwordPlano }}
                          </span>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin:0 0 24px 0;font-size:13px;line-height:1.5;color:#6b7280;background-color:#fefce8;border:1px solid #fde68a;border-radius:6px;padding:12px 16px;">
                <strong style="color:#92400e;">Importante:</strong> Por seguridad, te recomendamos cambiar tu contraseña la primera vez que ingreses al sistema.
              </p>

              {{-- CTA Button --}}
              <table width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td align="center" style="padding:8px 0 24px 0;">
                    <a href="{{ config('services.frontend.url') }}"
                       style="display:inline-block;background-color:#005DA4;color:#ffffff;font-size:14px;font-weight:700;text-decoration:none;padding:14px 36px;border-radius:8px;letter-spacing:0.5px;">
                      Ingresar al Simposio →
                    </a>
                  </td>
                </tr>
              </table>

              <p style="margin:0;font-size:13px;line-height:1.6;color:#374151;">
                Si tenés alguna consulta, podés responder este correo directamente.
                ¡Esperamos verte en el Simposio!
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
