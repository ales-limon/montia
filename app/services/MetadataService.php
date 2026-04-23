<?php
/**
 * Servicio de Metadatos Mejorado - LinkViewer
 * Extrae información de URLs con soporte para redirecciones y múltiples etiquetas
 */

class MetadataService {
    
    public function extract($url) {
        $data = [
            'titulo' => 'Sin título',
            'descripcion' => 'Sin descripción disponible.',
            'imagen_url' => '',
            'url' => $url
        ];

        try {
            $html = $this->fetchHtml($url);
            if (!$html) return $data;

            // Forzar UTF-8 y suprimir errores internos de libxml
            libxml_use_internal_errors(true);
            $doc = new DOMDocument();
            $doc->loadHTML('<?xml encoding="utf-8" ?>' . $html);
            $xpath = new DOMXPath($doc);
            libxml_clear_errors();

            // 1. EXTRAER TÍTULO (Prioridad: OpenGraph -> Twitter -> Title tag)
            $data['titulo'] = $this->getXpathValue($xpath, '//meta[@property="og:title"]/@content') 
                           ?? $this->getXpathValue($xpath, '//meta[@name="twitter:title"]/@content')
                           ?? $this->getXpathValue($xpath, '//title')
                           ?? 'Sin título';

            // 2. EXTRAER DESCRIPCIÓN
            $data['descripcion'] = $this->getXpathValue($xpath, '//meta[@property="og:description"]/@content') 
                                ?? $this->getXpathValue($xpath, '//meta[@name="twitter:description"]/@content')
                                ?? $this->getXpathValue($xpath, '//meta[@name="description"]/@content')
                                ?? 'Sin descripción disponible.';

            // 3. EXTRAER IMAGEN
            $data['imagen_url'] = $this->getXpathValue($xpath, '//meta[@property="og:image"]/@content')
                               ?? $this->getXpathValue($xpath, '//meta[@name="twitter:image"]/@content')
                               ?? '';

            // Limpieza de descripción simplificada
            if (strlen($data['descripcion']) > 250) {
                $data['descripcion'] = substr($data['descripcion'], 0, 247) . '...';
            }

            return $data;
        } catch (Exception $e) {
            error_log("Error extrayendo metadatos: " . $e->getMessage());
            return $data;
        }
    }

    private function fetchHtml($url) {
        $userAgent = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/110.0.0.0 Safari/537.36";
        
        // Estrategia para Instagram: Usar el bot de Facebook
        if (strpos($url, 'instagram.com') !== false) {
            $userAgent = "facebookexternalhit/1.1 (+http://www.facebook.com/externalhit_uatext.php)";
        }

        $options = [
            'http' => [
                'method' => "GET",
                'header' => "User-Agent: $userAgent\r\n" .
                            "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n" .
                            "Accept-Language: es-ES,es;q=0.8,en-US;q=0.5,en;q=0.3\r\n",
                'follow_location' => 1,
                'max_redirects' => 5,
                'timeout' => 15 // Aumentamos un poco el timeout para sitios lentos
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false,
            ]
        ];
        
        $context = stream_context_create($options);
        $content = @file_get_contents($url, false, $context);
        
        // Segundo intento: Si falla y no es Instagram, probamos con Googlebot
        if ($content === false && strpos($url, 'instagram.com') === false) {
            $options['http']['header'] = "User-Agent: Googlebot/2.1 (+http://www.google.com/bot.html)\r\n";
            $context = stream_context_create($options);
            $content = @file_get_contents($url, false, $context);
        }

        return $content;
    }

    private function getXpathValue($xpath, $query) {
        $nodes = $xpath->query($query);
        if ($nodes && $nodes->length > 0) {
            $val = trim($nodes->item(0)->nodeValue);
            return $val !== '' ? $val : null;
        }
        return null;
    }
}
