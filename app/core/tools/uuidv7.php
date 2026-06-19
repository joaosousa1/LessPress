<?php

function generate_uuidv7(): string 
{
    // 1. Obter o timestamp atual em milissegundos (48 bits)
    $time = (int) floor(microtime(true) * 1000);
    $timeHex = str_pad(dechex($time), 12, '0', STR_PAD_LEFT);

    // 2. Gerar bytes aleatórios para o restante do UUID
    $randomBytes = random_bytes(10);

    // 3. Ajustar os bits de versão (7) e variante (RFC 4122)
    // Versão 7 no byte 6
    $randomBytes[0] = chr((ord($randomBytes[0]) & 0x0f) | 0x70);
    // Variante no byte 8
    $randomBytes[2] = chr((ord($randomBytes[2]) & 0x3f) | 0x80);

    $randomHex = bin2hex($randomBytes);

    // 4. Montar o UUID v7 no formato padrão
    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($timeHex, 0, 8),
        substr($timeHex, 8, 4),
        substr($randomHex, 0, 4),
        substr($randomHex, 4, 4),
        substr($randomHex, 8, 12)
    );
}

// Teste (Note que se gerar vários, eles crescem em ordem alfabética/numérica)
// echo generate_uuidv7() . "\n";
// Exemplo: 018fba43-b6c8-7123-a456-426655443322


function get_date_from_uuidv7(string $uuid): string 
{
    // 1. Remove os hifens do UUID
    $cleanUuid = str_replace('-', '', $uuid);

    // 2. Pega os primeiros 12 caracteres hexadecimais (os 48 bits do timestamp)
    $timeHex = substr($cleanUuid, 0, 12);

    // 3. Converte de Hexadecimal para Decimal (Milissegundos Unix)
    $timeMs = hexdec($timeHex);

    // 4. Converte milissegundos para segundos para usar na função date()
    $timeSeconds = (int) ($timeMs / 1000);

    // 5. Formata a data no padrão que você desejar (ex: d/m/Y H:i:s)
    // Usamos 'u' para mostrar os milissegundos se quiser precisão total
    $milliseconds = $timeMs % 1000;
    
    return date('Y-m-d H:i:s', $timeSeconds) . '.' . sprintf('%03d', $milliseconds) . ' UTC';
}


//$userUuid = generate_uuidv7();
//$createAt = get_date_from_uuidv7($userUuid);
//echo $createAt . "\n";


