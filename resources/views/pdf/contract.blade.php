<!DOCTYPE html>
<html lang="es">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Contrato {{ $contract->contract_digital_code }}</title>
        <style>
            @page {
                margin: 18mm 15mm;
            }

            body {
                font-family: 'Helvetica', 'Arial', sans-serif;
                font-size: 11pt;
                color: #1a1a1a;
                line-height: 1.15;
                background: #ffffff;
            }

            .title-main {
                font-weight: 700;
                font-size: 14pt;
                text-align: center;
                text-transform: uppercase;
                margin: 0 0 6px 0;
            }

            .section-title {
                font-weight: 700;
                font-size: 11pt;
                text-transform: uppercase;
                margin: 16px 0 6px 0;
                letter-spacing: 0.5px;
            }

            .intro-text {
                text-align: justify;
                margin: 0 0 4px 0;
            }

            .mora-text {
                margin: 0 0 12px 0;
            }

            .mora-text strong {
                font-weight: 700;
            }

            /* Tablas de datos */
            .data-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }

            .data-table td {
                padding: 3px 4px;
                vertical-align: top;
                border-bottom: 1px solid #d0d0d0;
            }

            .data-table .label {
                font-weight: 700;
                width: 28%;
            }

            .data-table .value {
                width: 22%;
            }

            .data-table .label-sm {
                width: 20%;
            }

            .data-table .value-sm {
                width: 30%;
            }

            /* Checkboxes simétricos usando tabla */
            .checkbox-table {
                width: 100%;
                border-collapse: collapse;
                margin: 4px 0;
            }

            .checkbox-table td {
                padding: 2px 0;
                width: 33%;
                vertical-align: middle;
            }

            .checkbox-group {
                display: inline-block;
                font-size: 10pt;
                white-space: nowrap;
            }

            .checkbox-group .cb {
                display: inline-block;
                width: 12px;
                height: 12px;
                border: 1px solid #333;
                text-align: center;
                line-height: 12px;
                font-weight: 700;
                margin-right: 4px;
                background: #fff;
            }

            .checkbox-group .cb.checked {
                background: #e0e0e0;
            }

            /* Términos */
            .terms-box {
                font-size: 9.5pt;
                line-height: 1.5;
                text-align: justify;
                margin-top: 4px;
            }

            .terms-box p {
                margin: 4px 0;
            }

            .terms-box .term-number {
                font-weight: 700;
            }

            /* Pagaré */
            .pagare-box {
                margin-top: 18px;
                border: 1px solid #aaa;
                padding: 12px 16px;
                font-size: 10pt;
            }

            .pagare-box .pagare-title {
                font-weight: 700;
                font-size: 12pt;
                text-align: center;
                margin-bottom: 6px;
            }

            .pagare-box .pagare-field {
                display: inline-block;
                min-width: 70px;
                border-bottom: 1px solid #a0a0a0;
                padding: 0 6px;
                margin: 0 2px;
            }

            /* Firmas (una al lado de la otra) */
            .signature-row {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
            }

            .signature-box {
                flex: 1;
                text-align: center;
                border-top: 1px solid #333;
                padding-top: 6px;
                margin: 0 15px;
            }

            .signature-box .sig-image {
                max-width: 160px;
                max-height: 50px;
                margin: 0 auto 4px auto;
                display: block;
            }

            .signature-box .sig-label {
                font-size: 10pt;
                font-weight: 700;
            }

            .signature-box .sig-name {
                font-size: 10pt;
                font-weight: 700;
                margin-top: 2px;
            }

            .signature-box .sig-date {
                font-size: 8pt;
                color: #555;
            }

            /* Pie de página */
            .footer {
                text-align: center;
                font-size: 8.5pt;
                color: #333;
                margin-top: 24px;
                border-top: 1px solid #ccc;
                padding-top: 10px;
            }

            .footer .sucursal {
                font-weight: 700;
            }

            .footer .address-line {
                margin: 2px 0;
            }

            .footer .legal-note {
                font-size: 7.5pt;
                color: #666;
                margin-top: 4px;
            }

            .text-center {
                text-align: center;
            }

            .text-justify {
                text-align: justify;
            }

            .mt-1 {
                margin-top: 8px;
            }

            .mb-1 {
                margin-bottom: 8px;
            }

            .clearfix::after {
                content: "";
                clear: both;
                display: table;
            }

            .field-line {
                display: inline-block;
                border-bottom: 1px solid #a0a0a0;
                min-width: 100px;
                padding: 0 4px;
            }

            .field-line-sm {
                min-width: 60px;
            }

            .field-line-lg {
                min-width: 140px;
            }

            .data-table td.value {
                border-bottom: 1px solid #d0d0d0;
            }

            .placeholder {
                color: #555;
                font-family: 'Helvetica', 'Arial', sans-serif;
            }
        </style>
    </head>

    <body>

        <!-- ======== TÍTULO PRINCIPAL ======== -->
        <div class="title-main">
            CONTRATO PARA LA PRESTACION DE SERVICIOS DE TELECOMUNICACIONES<br>
            TELEVISION POR CABLE E INTERNET
        </div>

        <!-- ======== INTRODUCCIÓN ======== -->
        <p class="intro-text">
            Este contrato especifica las condiciones de común acuerdo entre el cliente y la empresa, en que se verifica
            la contratación de nuestro servicio, el cual no podrá ser alterado por error del cliente, cambio de parecer
            u otro motivo.
        </p>
        <p class="intro-text mora-text">
            El lapso de tiempo para el pago de su recibo del servicio prestado será de quince días y después se le
            cargará <strong>$3.00</strong> de mora por mes vencido.
        </p>

        <!-- ======== SECCIÓN PRIMERA ======== -->
        <div class="section-title">Sección Primera: Datos Generales del Cliente</div>

        <table class="data-table">
            <tr>
                <td class="label">Nombre Completo:</td>
                <td class="value">{{ $client->name ?? '' }}</td>
                <td class="label">NRC:</td>
                <td class="value">{{ $client->nrc ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Nombre Comercial:</td>
                <td class="value">{{ $client->commercial_name ?? '' }}</td>
                <td class="label"></td>
                <td class="value"></td>
            </tr>
            <tr>
                <td class="label">E-Mail:</td>
                <td class="value">{{ $client->email ?? '' }}</td>
                <td class="label">DUI:</td>
                <td class="value">{{ $client->document_number ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Fecha y lugar de expd.:</td>
                <td class="value" colspan="3">{{ $client->dui_expedition_date ?? '' }} {{ $client->dui_expedition_place ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">NIT:</td>
                <td class="value">{{ $client->nit ?? '' }}</td>
                <td class="label">Teléfonos:</td>
                <td class="value">{{ $client->phone ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Nacionalidad:</td>
                <td class="value" colspan="3">{{ $client->nationality ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Estado Civil:</td>
                <td class="value">{{ $client->marital_status ?? '' }}</td>
                <td class="label">Nombre del Cónyuge:</td>
                <td class="value">{{ $client->spouse_name ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Ocupación:</td>
                <td class="value">{{ $client->occupation ?? '' }}</td>
                <td class="label">Lugar de trabajo:</td>
                <td class="value">{{ $client->workplace ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Cargo:</td>
                <td class="value" colspan="3">{{ $client->position ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Ingreso mensual:</td>
                <td class="value">{{ $client->monthly_income ?? '' }}</td>
                <td class="label">Jefe inmediato:</td>
                <td class="value">{{ $client->boss_name ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Tel trabajo:</td>
                <td class="value" colspan="3">{{ $client->work_phone ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Dirección de trabajo:</td>
                <td class="value" colspan="3">{{ $client->work_address ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Dirección de instalación:</td>
                <td class="value" colspan="3">{{ $contract->installation_address ?? ($client->address ?? '') }}
                </td>
            </tr>
            <tr>
                <td class="label">Dirección de cobro:</td>
                <td class="value" colspan="3">{{ $client->billing_address ?? ($client->address ?? '') }}</td>
            </tr>
        </table>

        <!-- ======== SECCIÓN SEGUNDA ======== -->
        <div class="section-title">Sección Segunda: Especificaciones de los Servicios Prestados al Cliente</div>

        {{-- Tipo de contrato (checkboxes simétricos) --}}
        <div style="margin-bottom: 6px;">
            <span style="font-weight:700;">Tipo de contrato:</span>
            <table class="checkbox-table">
                <tr>
                    @php $tipo = $contract->contract_type ?? ''; @endphp
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipo == 'nuevo' ? 'checked' : '' }}">{{ $tipo == 'nuevo' ? 'X' : '' }}</span>
                            Nuevo</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipo == 'reconexion' ? 'checked' : '' }}">{{ $tipo == 'reconexion' ? 'X' : '' }}</span>
                            Reconexión</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipo == 'renovacion' ? 'checked' : '' }}">{{ $tipo == 'renovacion' ? 'X' : '' }}</span>
                            Renovación</span></td>
                </tr>
            </table>
        </div>

        {{-- Tipo de servicio --}}
        <div style="margin-bottom: 6px;">
            <span style="font-weight:700;">Tipo de servicio:</span>
            <table class="checkbox-table">
                <tr>
                    @php $tipoServ = $contract->service_type ?? ''; @endphp
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipoServ == 'residencial' ? 'checked' : '' }}">{{ $tipoServ == 'residencial' ? 'X' : '' }}</span>
                            Residencial</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipoServ == 'pyme' ? 'checked' : '' }}">{{ $tipoServ == 'pyme' ? 'X' : '' }}</span>
                            Pyme</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $tipoServ == 'corporativo' ? 'checked' : '' }}">{{ $tipoServ == 'corporativo' ? 'X' : '' }}</span>
                            Corporativo</span></td>
                </tr>
            </table>
        </div>

        {{-- Servicio contratado --}}
        <div style="margin-bottom: 6px;">
            <span style="font-weight:700;">Servicio contratado:</span>
            <table class="checkbox-table">
                <tr>
                    @php $servicio = $contract->service_contracted ?? ''; @endphp
                    <td><span class="checkbox-group"><span
                                class="cb {{ $servicio == 'cable_internet' ? 'checked' : '' }}">{{ $servicio == 'cable_internet' ? 'X' : '' }}</span>
                            Cable TV + internet</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $servicio == 'cable' ? 'checked' : '' }}">{{ $servicio == 'cable' ? 'X' : '' }}</span>
                            Cable TV</span></td>
                    <td><span class="checkbox-group"><span
                                class="cb {{ $servicio == 'internet' ? 'checked' : '' }}">{{ $servicio == 'internet' ? 'X' : '' }}</span>
                            Internet</span></td>
                </tr>
            </table>
        </div>

        {{-- Campos técnicos en tabla simétrica --}}
        <table class="data-table" style="margin-top:4px;">
            <tr>
                <td class="label" style="width:18%;">Tipo de acceso:</td>
                <td style="width:32%;">{{ $contract->access_type ?? '' }}</td>
                <td class="label" style="width:18%;">Velocidad:</td>
                <td style="width:32%;">{{ $contract->speed ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Tecnología:</td>
                <td colspan="3">{{ $contract->technology ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">MAC de modem:</td>
                <td>{{ $contract->modem_mac ?? '' }}</td>
                <td class="label">Serial del modem:</td>
                <td>{{ $contract->modem_serial ?? '' }}</td>
            </tr>
            <tr>
                <td class="label">Plazo de vigencia (meses):</td>
                <td>{{ $contract->term_months ?? '' }}</td>
                <td class="label">Costo de instalación:</td>
                <td>$ {{ number_format($contract->installation_cost ?? 0, 2) }}</td>
            </tr>
            <tr>
                <td class="label">Tarifa mensual unitaria:</td>
                <td>$ {{ number_format($contract->price ?? 0, 2) }}</td>
                <td class="label">Beneficio:</td>
                <td>{{ $contract->benefit ?? '' }}</td>
            </tr>
        </table>

        <!-- ======== SECCIÓN TERCERA ======== -->
        <div class="section-title">Sección Tercera: Términos y Condiciones</div>

        <div class="terms-box">
            <p>
                Los términos y condiciones para la prestación de Servicio de Telecomunicaciones, por parte de
                OMNIVISION-OMNICOM, Las condiciones particulares, en cuanto a plazo, plan o paquete contratado, tarifas,
                garantías, especificaciones de equipos para la prestación del servicio a cada cliente, se encuentran
                detalladas en este CONTRATO DE SERVICIO que el CLIENTE voluntariamente se suscribe y acepta.
            </p>

            {!! $contract->contract_terms ??
                '
                                            <p><span class="term-number">1- CLIENTE:</span> Declaro que recibiré de parte de OMNIVISION-OMNICOM el servicio de telecomunicaciones hasta la finalización del plazo acordado; y estoy consciente que el contrato de servicio entra en vigencia a partir de la fecha de suscripción.</p>
                                            <p><span class="term-number">2- TARIFAS Y PRECIOS:</span> Las tarifas y precios estarán consignadas en este contrato. Por el servicio que reciba me obligo a pagar a OMNIVISION-OMNICOM: I) Tarifa y Precio por el valor del paquete contratado. II) Precio por activación, instalación, desactivación, desinstalación, traslado de servicio, recargos por facturas vencidas y otros semejantes previamente informados. III) Precio por venta o arrendamiento de equipo.</p>
                                            <p><span class="term-number">3- FACTURACION:</span> Me comprometo a pagar los servicios antes indicados en dólares de los Estados Unidos de América, en concepto de servicios contratados, los cuales serán facturados por períodos mensuales de acuerdo al sistema de facturación utilizado por OMNIVISION - OMNICOM. Así mismo tengo el conocimiento que si al día del inicio del servicio faltare menos de un mes para la emisión de la factura correspondiente, los cargos básicos se me facturarán proporcional. También deberé pagar dicha factura o crédito fiscal como máximo en la fecha última de pago que se me ha indicado por cualquier medio verificable que disponga la empresa; debiéndose cancelar en las oficinas administrativas, cobradores, etc. La falta de recibir el documento de cobro correspondiente, no me exime de la responsabilidad del pago oportuno.</p>
                                            <p><span class="term-number">4- VIGENCIA Y PLAZO:</span> El plazo obligatorio de vigencia aplicable al servicio de cable tv e Internet, prestado por OMNIVISION-OMNICOM se estipula en este contrato de servicio que suscribo y entrará en vigencia a partir de la fecha de mi suscripción, luego de finalizado el plazo obligatorio.</p>
                                            <p><span class="term-number">5- TERMINACION CONTRACTUAL Y CONDICIONES DE RETIRO ANTICIPADO:</span> En caso de dar por terminado el contrato de servicio tv e Internet, dentro del plazo obligatorio establecido en el presente contrato, debo de notificar por escrito a las oficinas administrativas con diez días hábiles de anticipación al retiro efectivo del servicio, deberé pagar todos y cada uno de los montos adecuados al momento de la terminación (Valor del número de meses restante para la finalización del contrato), y penalidades por terminación anticipada de manera particular.</p>
                                            <p><span class="term-number">6- EL SERVICIO CONTRATADO PODRA SUSPENDERSE EN LOS CASOS SIGUIENTES:</span> OMNIVISION-OMNICOM, podrá suspender la prestación de servicio de cable tv e Internet por incumplimiento de cualquiera de las obligaciones establecidas en el contrato, especialmente por mora de una factura o crédito fiscal por servicio prestado, por casos establecidos en la ley y su respectivo reglamento, de presentarse esta situación se debe solicitar, al cliente mediante notificaciones por escrito llamadas telefónicas, correos electrónicos o por cualquier otro medio; La cancelación en el servicio por parte de "EL CLIENTE" no lo exime del pago de las cantidades adeudadas. Este deberá cubrirlas al 100% CIEN POR CIENTO "al momento de la cancelación"; así mismo cancelara LA SUMA DE LOS MESES PENDIENTES; cuando falte para la finalización del contrato; de igual manera permitir el retiro del equipo suministrado por el PROVEEDOR y de las instalaciones realizadas en el domicilio de "EL CLIENTE".</p>
                                            <p><span class="term-number">7- EQUIPO ENTREGADO EN COMODATO:</span> a) Recibí de parte de OMNIVISION-OMNICOM en entera satisfacción y en calidad de comodato el equipo que permitirá recibir el servicio de cable tv e internet, que será instalado a una distancia no mayor de dos metros de la computadora. Me comprometo a mantenerlo conectado al protector/regulador de voltaje correspondiente y con instalaciones polarizadas, tengo claro que el equipo y accesorios instalados, para el servicio son propiedad de OMNIVISION-OMNICOM, b) Es mi responsabilidad el mantenimiento y cuidado del equipo por uso normal, o por uso indebido o irregular durante el tiempo del contrato vigente, es responsabilidad de la empresa si son defectos de fábrica, mala calidad o condiciones ruinosas del equipo al inicio de la vigencia del contrato, ninguna de las partes es responsable por interrupciones en el servicio causa de sucesos constitutivos de fuerza mayor o por caso fortuito. c) se entenderá que el equipo se encontrara en la dirección proporcionada por el cliente cuando se elaboró el contrato de servicio, por lo tanto el compromiso de OMNIVISION-OMNICOM es brindar el servicio contratado en dicha dirección. d) Me comprometo a devolver el equipo indicado en el contrato al final del plazo, debiendo entregarlo al personal de OMNIVISION-OMNICOM designado para tales efectos en buen estado de conservación y funcionamiento. e) En caso de hurto, robo o pérdida del equipo notificare OMNIVISION-OMNICOM para el bloqueo del servicio y me obligo a presentar la denuncia correspondiente ante las autoridades competentes, haciendo llegar una copia certificada a las oficinas administrativas. f) Reposición, en caso de deteriorado, robo o pérdida, entre otras causas del equipo, el cliente podrá solicitar la reposición del mismo pagando el valor total del equipo. g) Prohibición, el cliente no podrá arrendar ni ceder los derechos emanados del equipo, ni aun, a título gratuito, ni comprometer el dominio o posesión del mismo en forma alguna.</p>
                                            <p><span class="term-number">8- CONDICIONES ESPECIALES DE CONTRATACION DE SERVICIOS DE INTERNET:</span> El servicio de internet será prestado bajo las siguientes condiciones: a) El cliente podrá utilizar el servicio únicamente desde el número de protocolo de interconexión asignada por la empresa y bajo los requerimientos técnicos que se indiquen al efecto, b) El servicio se prestará en forma continua, las 24 horas del día, todo el año durante el plazo de vigencia del presente contrato; salvo mora en el pago de servicios por el cliente o en caso fortuito de fuerza mayor; la capacidad del servicio prestado será hasta el máximo de la velocidad estable en el plan seleccionado; la velocidad de navegación podrá variar por diversos factores técnicos a OMNIVISION-OMNICOM tales como: características técnicas de equipo y software del cliente cantidad de usuarios conectados a la red, franjas horarias, entre otros similares, c) El cliente garantiza las instalaciones eléctricas, equipos de protección asociados y el equipo informático adecuado para acceder al servicio. El cliente es responsable del uso indebido de información por medio de servicio de internet.</p>
                                            <p><span class="term-number">9- OBLIGACIONES DE OMNIVISION-OMNICOM:</span> a) suministrar el servicio de Internet y Cable T V, bajo las condiciones establecidas en el presente contrato, b) obligaciones Legales, todas las indicadas en las leyes y reglamentos aplicables, c) A brindar una respuesta clara y oportuna cuando el cliente presente reclamos, quejas o cualquier otro tipo de comunicación por los medios establecidos por la empresa, d) A reintegrar en próxima factura, cantidades que fueron cobradas de forma contraria a los precios, tarifas y penalidades pactadas.</p>
                                            <p><span class="term-number">10- OBLIGACIONES DEL CLIENTE:</span> Son obligaciones a mi cargo, a) Cargos, pagar puntualmente en la fecha que me corresponde los cargos, de la prestación de servicios de cable tv e Internet, así como también los recargos generados por pagos tardíos, luego de transcurrido la fecha de vencimiento de la factura, para lo cual deberá utilizar los medios y/o lugares señalados por OMNIVISION-OMNICOM para tales efectos, b) Las obligaciones legales, indicadas en las leyes y reglamentos aplicables, c) Me obligo a no utilizar las redes de telecomunicaciones de OMNIVISION-OMNICOM para actividades contrarias a la ley, la moral y el orden público ni a congestionar o dañar el uso de las redes de forma que pudiera afectar la prestación de los servicios a otros usuarios, a no interferir, modificar o alterar cualquier de los activos prestados por OMNIVISION-OMNICOM para la propagación de servicios de manera ilegal. Cuidado de los equipos, Acepto y reconozco que el equipo utilizado para la prestación del servicio contratado, son de exclusiva propiedad de OMNIVISION-OMNICOM, por lo que acepto la responsabilidad, buen uso y conservación adecuada. En caso de extravió, daños o destrucción de equipos, es mi responsabilidad el mantenimiento y reposición del equipo.</p>
                                            <p style="margin-top:6px;">Reconozco que el equipo, accesorios y cableado instalado en la dirección que solicite la prestación del servicio de Telecomunicaciones, lo recibo en óptimas condiciones de funcionamiento.</p>
                                            <p><span class="term-number">11- ES RESPONSABILIDAD DEL CLIENTE:</span> El cuido de la Red y Equipo que la empresa Omnivisión proporciona; luego de su instalación; ya que no nos haremos responsables por el daño que sea causado con dolo por la parte contratante; llámese este "Cliente", siempre y cuando el personal encargado (Técnicos, previa visita) lo manifieste y así mismo se le hara saber al cliente, luego del diagnóstico presencial que nuestro personal realice en su domicilio.</p>
                                            ' !!}
        </div>

        <!-- ======== FECHA DE PAGO ======== -->
        <div style="margin-top:18px;">
            <p style="margin:4px 0; font-weight:700; text-align:right;">Fecha de pago</p>
            <p style="margin:4px 0;">
                Fecha: Chalatenango a los <span class="placeholder">______</span> días del mes de
                <span class="placeholder">_________________</span> año
                <span class="placeholder">_________</span>
            </p>
        </div>

        <!-- ======== NOTA ======== -->
        <p style="font-size:9pt; font-style:italic; margin:6px 0;">
            Nota: El uso de la señal de telecomunicaciones es exclusivo para la persona que lo contrata por ningún
            motivo podrá compartir la señal de lo contrario se suspenderá el servicio y será demandado por los daños
            correspondientes a nuestra empresa.
        </p>

        <!-- ======== FIRMAS (2 columnas) ======== -->
        <table style="width:100%; border-collapse:collapse; margin-top:20px;">
            <tr>
                <td style="width:50%; text-align:center; vertical-align:top; padding:0 20px;">
                    @if ($clientSignature)
                        <img src="{{ $clientSignature }}" style="max-width:160px; max-height:50px; margin:0 auto 4px auto; display:block;" alt="Firma del Cliente">
                    @endif
                    <div style="margin:10px 0 4px 0; font-size:12pt;">______________________</div>
                    <div style="font-size:10pt; font-weight:700;">Firma Cliente</div>
                    <div style="font-size:10pt; font-weight:700; margin-top:2px;">{{ $client->name ?? '' }}</div>
                    <div style="font-size:8pt; color:#555;">{{ $contract->signed_at ? $contract->signed_at->format('d/m/Y h:i A') : '' }}</div>
                </td>
                <td style="width:50%; text-align:center; vertical-align:top; padding:0 20px;">
                    @if ($salesRepSignature)
                        <img src="{{ $salesRepSignature }}" style="max-width:160px; max-height:50px; margin:0 auto 4px auto; display:block;" alt="Firma Autorizada">
                    @endif
                    <div style="margin:10px 0 4px 0; font-size:12pt;">______________________</div>
                    <div style="font-size:10pt; font-weight:700;">Firma Autorizada</div>
                    <div style="font-size:10pt; font-weight:700; margin-top:2px;">{{ $creator->name ?? '' }}</div>
                    <div style="font-size:8pt; color:#555;">{{ now()->format('d/m/Y h:i A') }}</div>
                </td>
            </tr>
        </table>

        <!-- ======== PIE DE PÁGINA (3 columnas) ======== -->
        <div class="footer" style="text-align:center;">
            <table style="width:100%; border-collapse:collapse; font-size:7.5pt;">
                <tr>
                    <td style="width:33%; vertical-align:top; padding:4px 8px; text-align:center;">
                        <span style="font-weight:700;">CASA MATRIZ</span><br>
                        Calle placido peña local #2, Bo. Las Flores, Pórtico San José, Chalatenango, Chalatenango.<br>
                        Teléfonos: 2301-2150 / 7733-8982
                    </td>
                    <td style="width:33%; vertical-align:top; padding:4px 8px; text-align:center;">
                        <span style="font-weight:700;">SUCURSAL AMAYO</span><br>
                        Caserío Amayo, Tejutla, Carretera a Chalatenango.<br>
                        Tel.: 2300-5614 / Cel.: 7870-9591 / Tel.: 2417-6468
                    </td>
                    <td style="width:33%; vertical-align:top; padding:4px 8px; text-align:center;">
                        <span style="font-weight:700;">SUCURSAL LA PALMA</span><br>
                        1a. Calle Ote. Bo. El Centro # 14, Frente al Parque.<br>
                        Cel.: 7870-9928
                    </td>
                </tr>
            </table>
            <div style="font-size:7pt; color:#666; margin-top:6px;">
                Documento generado electrónicamente el {{ now()->format('d/m/Y \a \l\a\s h:i A') }} |
                Código: {{ $contract->contract_digital_code }} |
                Válido sin firma autógrafa según Ley de Firma Electrónica de El Salvador.
            </div>
        </div>

        <!-- ======== PAGARÉ SIN PROTESTA (NUEVA PÁGINA) ======== -->
        <div style="page-break-before: always; margin-top: 20px;"></div>

        <div class="pagare-box">
            <div class="pagare-title">PAGARE SIN PROTESTO</div>
            <p style="margin:4px 0;">
                <strong>POR US $</strong>
                <span class="pagare-field"
                    style="min-width:100px;">{{ number_format($contract->price ?? 0, 2) }}</span>
            </p>
            <p style="margin:4px 0;">
                En la Ciudad de Chalatenango, de
                <span class="pagare-field"
                    style="min-width:30px;">{{ $contract->contract_date ? $contract->contract_date->day : '' }}</span>
                de
                <span class="pagare-field"
                    style="min-width:80px;">{{ $contract->contract_date ? $contract->contract_date->monthName : '' }}</span>
                de
                <span class="pagare-field"
                    style="min-width:50px;">{{ $contract->contract_date ? $contract->contract_date->year : '' }}</span>
            </p>
            <p style="margin:4px 0;">
                Pagare(mos) en forma incondicional a la orden de <strong>OMNIVISION-OMNICOM</strong>, en Chalatenango,
                Chalatenango, el día
                <span class="pagare-field" style="min-width:30px;">{{ now()->addDays(30)->day }}</span>
                de
                <span class="pagare-field" style="min-width:80px;">{{ now()->addDays(30)->monthName }}</span>
                de
                <span class="pagare-field" style="min-width:50px;">{{ now()->addDays(30)->year }}</span>
                la cantidad de $ <span class="pagare-field"
                    style="min-width:100px;">{{ number_format($contract->price ?? 0, 2) }}</span>
            </p>
            <p style="margin:4px 0; font-size:9.5pt;">
                En caso de que no fuere pagado a su vencimiento, pagare (mos) además a partir de esta fecha el interés
                moratorio del
                <span class="pagare-field" style="min-width:30px;">5</span> % mensual.
                Para los efectos legales de esta obligación mercantil fijo (amos) como domicilio especial la ciudad de
                <span class="pagare-field" style="min-width:120px;">Chalatenango</span>
                a cuyos tribunales me (nos) someto (emos) expresamente y en caso de acción judicial, renuncio (amos) al
                derecho de apelar del decreto de embargo, Sentencia de remate y de toda otra providencia apelable que se
                dictare en el juicio ejecutivo mercantil y sus incidencias, siendo a mi (nuestro) cargo, cualquier gasto
                que OMNIVISION-OMNICOM hiciere en el cobro de este pagaré, incluso los llamados personales, aun cuando
                por regla general no hubiere condenación en costas; así mismo faculto (amos) a OMNIVISION-OMNICOM para
                que designe a la persona depositaria de los bienes que se embarguen, a quien relevo (amos) de la
                obligación de rendir fianza y cuenta.
            </p>
            <table style="width:100%; margin-top:10px; font-size:10pt;">
                <tr>
                    <td style="width:15%;"><strong>NOMBRE:</strong></td>
                    <td style="border-bottom:1px solid #a0a0a0; padding-left:6px;">{{ $client->name ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>DIRECCIÓN:</strong></td>
                    <td style="border-bottom:1px solid #a0a0a0; padding-left:6px;">{{ $client->address ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>D.U.I.:</strong></td>
                    <td style="border-bottom:1px solid #a0a0a0; padding-left:6px;">{{ $client->document_number ?? '' }}</td>
                </tr>
                <tr>
                    <td><strong>FIRMA:</strong></td>
                    <td style="border-bottom:1px solid #a0a0a0; padding-left:6px; height:30px;"></td>
                </tr>
            </table>
        </div>

    </body>

</html>

