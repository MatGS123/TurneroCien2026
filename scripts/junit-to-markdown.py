#!/usr/bin/env python3
"""Convierte el XML JUnit de Pest en un informe breve y verificable."""
from __future__ import annotations

import datetime as dt
import html
import pathlib
import re
import sys
import xml.etree.ElementTree as ET

xml_path = pathlib.Path(sys.argv[1])
markdown_path = pathlib.Path(sys.argv[2])
exit_code = int(sys.argv[3]) if len(sys.argv) > 3 else 1
raw_path = pathlib.Path(sys.argv[4]) if len(sys.argv) > 4 else None

now = dt.datetime.now().astimezone().strftime('%Y-%m-%d %H:%M:%S %z')
lines = [
    '# Informe de pruebas técnicas — CIEN Formosa',
    '',
    f'**Fecha:** {now}  ',
    '**Herramienta:** Pest 3 / PHPUnit  ',
    f"**Resultado:** {'APROBADO' if exit_code == 0 else 'REQUIERE REVISIÓN'}",
    '',
]

try:
    root = ET.parse(xml_path).getroot()
    cases = root.findall('.//testcase')
    tests = len(cases)
    failures = sum(case.find('failure') is not None for case in cases)
    errors = sum(case.find('error') is not None for case in cases)
    skipped = sum(case.find('skipped') is not None for case in cases)
    passed = tests - failures - errors - skipped
    total_time = sum(float(case.attrib.get('time', 0) or 0) for case in cases)

    lines += [
        '## Resumen',
        '',
        '| Indicador | Resultado |',
        '|---|---:|',
        f'| Pruebas ejecutadas | {tests} |',
        f'| Aprobadas | {passed} |',
        f'| Fallidas | {failures} |',
        f'| Errores | {errors} |',
        f'| Omitidas | {skipped} |',
        f'| Tiempo total | {total_time:.3f} s |',
        '',
        '## Pruebas ejecutadas',
        '',
        '| Prueba | Estado | Tiempo |',
        '|---|---|---:|',
    ]

    problems: list[str] = []
    for case in cases:
        name = case.attrib.get('name', 'Prueba sin nombre')
        duration = float(case.attrib.get('time', 0) or 0)
        if case.find('failure') is not None:
            status = 'FALLÓ'
        elif case.find('error') is not None:
            status = 'ERROR'
        elif case.find('skipped') is not None:
            status = 'OMITIDA'
        else:
            status = 'OK'
        safe_name = html.escape(name.replace('|', '\\|'))
        lines.append(f'| {safe_name} | {status} | {duration:.3f} s |')

        problem_node = case.find('failure') or case.find('error')
        if problem_node is not None:
            message = problem_node.attrib.get('message', '') or ''.join(problem_node.itertext())
            message = re.sub(r'\s+', ' ', message).strip()
            if message:
                problems.append(f"**{name}:** {message[:300]}")

    if problems:
        lines += ['', '## Incidencias resumidas', '']
        lines.extend(f'- {problem}' for problem in problems)

    lines += [
        '',
        '## Evidencia complementaria',
        '',
        'El XML JUnit y la salida completa de Pest se conservan por separado en la misma carpeta. Este documento resume únicamente las cifras y las incidencias principales para facilitar su incorporación al TIF.',
    ]
except (FileNotFoundError, ET.ParseError) as exc:
    lines += [
        '## Resultado',
        '',
        f'No se pudo leer el XML JUnit: `{exc}`.',
    ]

if raw_path:
    lines += ['', f'**Salida cruda:** `{raw_path}`  ', f'**XML JUnit:** `{xml_path}`']

markdown_path.parent.mkdir(parents=True, exist_ok=True)
markdown_path.write_text('\n'.join(lines) + '\n', encoding='utf-8')
print(markdown_path)
