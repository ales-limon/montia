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

        // Soporte Especial para TikTok (vía OEmbed)
        if (strpos($url, 'tiktok.com') !== false) {
            $tiktokData = $this->fetchTikTokOEmbed($url);
            if ($tiktokData) return array_merge($data, $tiktokData);
        }

        // Soporte Especial para X / Twitter (vía OEmbed)
        if (strpos($url, 'twitter.com') !== false || strpos($url, 'x.com') !== false) {
            $xData = $this->fetchXOEmbed($url);
            if ($xData) return array_merge($data, $xData);
        }

        // Soporte Especial para YouTube (vía OEmbed) - Más fiable en producción
        if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
            $ytData = $this->fetchYouTubeOEmbed($url);
            if ($ytData) return array_merge($data, $ytData);
        }

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
            $rawTitulo = $this->getXpathValue($xpath, '//meta[@property="og:title"]/@content')
                      ?? $this->getXpathValue($xpath, '//meta[@name="twitter:title"]/@content')
                      ?? $this->getXpathValue($xpath, '//title')
                      ?? 'Sin título';
            $data['titulo'] = $this->cleanSocialTitle($rawTitulo);

            // 2. EXTRAER DESCRIPCIÓN
            $data['descripcion'] = $this->getXpathValue($xpath, '//meta[@property="og:description"]/@content') 
                                ?? $this->getXpathValue($xpath, '//meta[@name="twitter:description"]/@content')
                                ?? $this->getXpathValue($xpath, '//meta[@name="description"]/@content')
                                ?? 'Sin descripción disponible.';

            // 3. EXTRAER IMAGEN
            $img = $this->getXpathValue($xpath, '//meta[@property="og:image"]/@content')
                ?? $this->getXpathValue($xpath, '//meta[@name="twitter:image"]/@content')
                ?? $this->getXpathValue($xpath, '//link[@rel="apple-touch-icon"]/@href')
                ?? $this->getXpathValue($xpath, '//link[@rel="icon"]/@href')
                ?? $this->getXpathValue($xpath, '//link[@rel="shortcut icon"]/@href')
                ?? $this->getXpathValue($xpath, '//img[not(contains(@src, "data:image"))]/@src') // Primera imagen real
                ?? '';

            $data['imagen_url'] = $this->resolveUrl($img, $url);

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

    /**
     * Resuelve una URL relativa a absoluta
     */
    private function resolveUrl($url, $base) {
        if (!$url || parse_url($url, PHP_URL_SCHEME) != '') return $url;
        
        $parts = parse_url($base);
        $baseUrl = $parts['scheme'] . '://' . $parts['host'];
        
        if (strpos($url, '/') === 0) {
            return $baseUrl . $url;
        } else {
            $path = dirname($parts['path'] ?? '');
            return $baseUrl . ($path == '/' || $path == '\\' ? '' : $path) . '/' . $url;
        }
    }

    /**
     * Verifica si el contenido extraído es seguro
     */
    public function checkContentSafety($data) {
        $blackList = [
            'porno', 'porn', 'sexo', 'sexual', 'hentai', 'xvideo', 'xhamster',
            'droga', 'narco', 'sicario', 'gore', 'suicidio', 'muerte', 'asesinato',
            'apuesta', 'casino', 'bet', 'estafa', 'scam'
        ];
        
        $textToClean = strtolower($data['titulo'] . ' ' . $data['descripcion'] . ' ' . $data['url']);
        
        foreach ($blackList as $word) {
            // Usar expresiones regulares con límites de palabra (\b) para evitar falsos positivos
            // Ejemplo: 'bet' no bloqueará 'diabetes'
            $pattern = "/\b" . preg_quote($word, '/') . "\b/i";
            if (preg_match($pattern, $textToClean)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Extraer metadatos de TikTok usando su API oficial de OEmbed
     */
    private function fetchTikTokOEmbed($url) {
        $apiBase = "https://www.tiktok.com/oembed?url=" . urlencode($url);
        $json = $this->fetchHtml($apiBase);
        
        if ($json) {
            $decoded = json_decode($json, true);
            if ($decoded) {
                return [
                    'titulo' => $decoded['title'] ?? 'TikTok Video',
                    'descripcion' => "Video de TikTok por " . ($decoded['author_name'] ?? 'Usuario'),
                    'imagen_url' => $decoded['thumbnail_url'] ?? ''
                ];
            }
        }
        return null;
    }

    /**
     * Extraer metadatos de X (Twitter) usando su API oficial de OEmbed
     */
    private function fetchXOEmbed($url) {
        $apiBase = "https://publish.twitter.com/oembed?url=" . urlencode($url);
        $json = $this->fetchHtml($apiBase);
        
        if ($json) {
            $decoded = json_decode($json, true);
            if ($decoded) {
                // Limpiar el HTML para extraer el texto del tweet si es posible
                $text = strip_tags($decoded['html']);
                // El texto suele venir con el autor al final, lo limpiamos un poco
                $text = explode('—', $text)[0];

                $data = [
                    'titulo' => (isset($decoded['author_name']) ? "Tweet de " . $decoded['author_name'] : 'Post en X'),
                    'descripcion' => trim($text),
                    'imagen_url' => $decoded['thumbnail_url'] ?? ''
                ];

                // Intento de rescate de imagen si OEmbed no la dio
                if (empty($data['imagen_url'])) {
                    // Primero intentamos buscar el og:image con el bot
                    $html = $this->fetchHtml($url, "Twitterbot/1.0");
                    if ($html) {
                        preg_match('/<meta[^>]*property=["\']og:image["\'][^>]*content=["\']([^"\']+)["\']/', $html, $matches);
                        if (isset($matches[1])) {
                            $data['imagen_url'] = $matches[1];
                        } else {
                            // Si falla, buscamos cualquier icono o favicon para que no quede vacío
                            preg_match('/<link[^>]*rel=["\'](?:icon|shortcut icon|apple-touch-icon)["\'][^>]*href=["\']([^"\']+)["\']/', $html, $matches);
                            if (isset($matches[1])) {
                                $data['imagen_url'] = $this->makeAbsolute($url, $matches[1]);
                            }
                        }
                    }
                }

                // Fallback final: Si sigue vacío, ponemos un logo de X premium para mantener la estética
                if (empty($data['imagen_url'])) {
                    $data['imagen_url'] = "https://abs.twimg.com/errors/logo46x38.png"; // Icono oficial de X como último recurso
                }

                return $data;
            }
        }
        return null;
    }

    /**
     * Extraer metadatos de YouTube usando su API oficial de OEmbed
     */
    private function fetchYouTubeOEmbed($url) {
        $apiBase = "https://www.youtube.com/oembed?url=" . urlencode($url) . "&format=json";
        $json = $this->fetchHtml($apiBase);
        
        if ($json) {
            $decoded = json_decode($json, true);
            if ($decoded) {
                return [
                    'titulo' => $decoded['title'] ?? 'YouTube Video',
                    'descripcion' => ($decoded['author_name'] ?? 'YouTube') . " - Video",
                    'imagen_url' => $decoded['thumbnail_url'] ?? ''
                ];
            }
        }
        return null;
    }

    protected function fetchHtml($url, $customUserAgent = null) {
        // User-Agent por defecto
        $userAgent = $customUserAgent ?? "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/112.0.0.0 Safari/537.36";
        
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

    /**
     * Elimina el prefijo de stats de engagement que Facebook/Meta pone en og:title
     * según el idioma de la IP del servidor (ruso, alemán, etc.)
     * Ej: "1 млн просмотров - 28 тыс. реакций | Título real" → "Título real"
     */
    private function cleanSocialTitle($title) {
        if (strpos($title, ' | ') !== false) {
            $parts = explode(' | ', $title);
            $lastPart = trim(end($parts));
            if (strlen($lastPart) > 3) {
                return $lastPart;
            }
        }
        return $title;
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
