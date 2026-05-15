<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KnowledgeBaseArticle;
use App\Models\ServiceType;

class KnowledgeBaseSeeder extends Seeder
{
    public function run()
    {
        // Asegurar que los tipos de servicio existen (ya deben estar por el ServiceTypeSeeder)
        $revision = ServiceType::where('name', 'revision')->first();
        $verificacion = ServiceType::where('name', 'verificacion_tecnica')->first();
        $instalacion = ServiceType::where('name', 'instalacion')->first();
        $reconexion = ServiceType::where('name', 'reconexion')->first();
        $cobro = ServiceType::where('name', 'cobro_pendiente')->first();
        $traslado = ServiceType::where('name', 'traslado')->first();

        // Artículo 1: Verificación Técnica de Señal
        $article1 = KnowledgeBaseArticle::firstOrCreate(
            ['title' => 'Procedimiento de Verificación Técnica de Señal'],
            [
                'content' => "**Objetivo:** Diagnosticar y resolver problemas de señal en el equipo del cliente.\n\n**Pasos a seguir:**\n1. Solicitar al cliente que verifique las conexiones de los cables coaxiales y de red.\n2. Preguntar si hay otros dispositivos conectados al mismo splitter.\n3. Pedir al cliente que reinicie el router y el decodificador.\n4. Si el problema persiste, verificar los niveles de señal desde el panel NOC.\n5. Si los niveles son bajos (< -10 dBmV), programar una visita técnica con prioridad P2.\n6. Si los niveles son normales, guiar al cliente en la restauración de fábrica del equipo.\n\n**Material requerido:**\n- Medidor de señal portátil.\n- Cable coaxial de repuesto (3m).\n- Conectores F de compresión.\n\n**Notas:** Este procedimiento aplica para los servicios de Revisión y Verificación Técnica. No requiere la creación de una OT a menos que se detecte un daño físico.",
                'priority' => 'P3',
                'category' => 'Procedimiento Técnico',
            ]
        );
        if ($revision) $article1->serviceTypes()->syncWithoutDetaching([$revision->id]);
        if ($verificacion) $article1->serviceTypes()->syncWithoutDetaching([$verificacion->id]);

        // Artículo 2: Instalación de Router
        $article2 = KnowledgeBaseArticle::firstOrCreate(
            ['title' => 'Instalación de Router'],
            [
                'content' => "**Objetivo:** Instalar y configurar un router nuevo en el domicilio del cliente.\n\n**Pasos a seguir:**\n1. Desembalar el router y verificar que incluya fuente de alimentación y cable de red.\n2. Conectar el cable de red desde el puerto WAN del router al módem.\n3. Encender el router y esperar a que las luces de encendido y conexión estén estables.\n4. Acceder a la interfaz de configuración (192.168.1.1) y establecer el SSID y contraseña proporcionados por la empresa.\n5. Verificar la conectividad a internet desde un dispositivo móvil o portátil.\n6. Informar al cliente sobre el nombre de la red y la contraseña.\n\n**Material estándar:**\n- Router.\n- Cable de red de 1.5m.\n- Adaptador de corriente.\n\n**Notas:** Si el cliente solicita configuración avanzada (apertura de puertos, DMZ), escalar al NOC.",
                'priority' => 'P2',
                'category' => 'Instalación',
            ]
        );
        if ($instalacion) $article2->serviceTypes()->syncWithoutDetaching([$instalacion->id]);

        // Artículo 3: Reconexión de Servicio
        $article3 = KnowledgeBaseArticle::firstOrCreate(
            ['title' => 'Reconexión de Servicio'],
            [
                'content' => "**Objetivo:** Restablecer el servicio de internet/cable tras un corte por falta de pago o mantenimiento.\n\n**Pasos a seguir:**\n1. Verificar en el sistema que el cliente ha regularizado su situación de pago.\n2. Acceder al nodo correspondiente y activar la línea desde el puerto del cliente.\n3. Realizar una llamada de cortesía para confirmar que el servicio está funcionando.\n4. Si el cliente reporta problemas, seguir el procedimiento de verificación técnica.\n\n**Prioridad:** P1 (Crítico) – El restablecimiento debe realizarse en un plazo máximo de 2 horas.\n\n**Notas:** Este procedimiento es exclusivo para reconexiones. No aplica para nuevas instalaciones.",
                'priority' => 'P1',
                'category' => 'Emergencia',
            ]
        );
        if ($reconexion) $article3->serviceTypes()->syncWithoutDetaching([$reconexion->id]);

        // Artículo 4: Cobro Pendiente
        $article4 = KnowledgeBaseArticle::firstOrCreate(
            ['title' => 'Atención de Cobro Pendiente'],
            [
                'content' => "**Objetivo:** Informar al cliente sobre su saldo pendiente y opciones de pago.\n\n**Procedimiento:**\n1. Saludar cordialmente y verificar la identidad del cliente con su número de cuenta o DUI.\n2. Informar el monto adeudado y la fecha de vencimiento.\n3. Ofrecer opciones de pago: en línea, transferencia bancaria o pago en sucursal.\n4. Si el cliente paga durante la llamada, seguir el procedimiento de reconexión si aplica.\n5. Agendar una llamada de seguimiento si el cliente no puede pagar inmediatamente.\n\n**Guión sugerido:** \"Su saldo pendiente es de \$XX.XX con vencimiento el día XX. Puede realizar su pago a través de...\"",
                'priority' => 'P4',
                'category' => 'Administrativo',
            ]
        );
        if ($cobro) $article4->serviceTypes()->syncWithoutDetaching([$cobro->id]);

        // Artículo 5: Traslado de Equipo
        $article5 = KnowledgeBaseArticle::firstOrCreate(
            ['title' => 'Traslado de Equipo a Nueva Dirección'],
            [
                'content' => "**Objetivo:** Desinstalar y reinstalar el equipo del cliente en una nueva ubicación.\n\n**Pasos a seguir:**\n1. Confirmar la nueva dirección y la fecha acordada con el cliente.\n2. En la visita, desinstalar cuidadosamente todo el equipo (router, decodificador, antenas).\n3. Empaquetar los dispositivos en sus cajas originales o material de protección.\n4. Transportar el equipo a la nueva dirección.\n5. Realizar el procedimiento de instalación estándar en la nueva ubicación.\n6. Verificar el funcionamiento de todos los servicios.\n\n**Material adicional sugerido:**\n- Cinta adhesiva para embalaje.\n- Bolsas para accesorios pequeños (cables, controles).\n\n**Notas:** Este servicio puede requerir una OT específica si hay tendido de cable nuevo.",
                'priority' => 'P3',
                'category' => 'Logística',
            ]
        );
        if ($traslado) $article5->serviceTypes()->syncWithoutDetaching([$traslado->id]);

        $this->command->info('✅ Artículos de Base de Conocimiento creados correctamente.');
    }
}