#!/usr/bin/env bash
set -u

REPORT_DIR="${TIF_REPORT_DIR:-storage/testing/reports}"
mkdir -p "$REPORT_DIR"

STAMP="$(date +%Y%m%d-%H%M%S)"
XML="$REPORT_DIR/pest-$STAMP.junit.xml"
RAW="$REPORT_DIR/pest-$STAMP.txt"
MD="$REPORT_DIR/informe-tif-$STAMP.md"

printf '%s\n' '========================================'
printf '%s\n' 'CIEN FORMOSA — PRUEBAS TÉCNICAS PEST'
printf 'Inicio: %s\n' "$(date '+%Y-%m-%d %H:%M:%S %z')"
printf '%s\n' '========================================'

set +e
vendor/bin/pest --testsuite Unit,Feature --log-junit="$XML" --testdox > >(tee "$RAW") 2>&1
PEST_STATUS=${PIPESTATUS[0]}
set -e

python3 scripts/junit-to-markdown.py "$XML" "$MD" "$PEST_STATUS" "$RAW" >/dev/null

printf '\n%s\n' '----------------------------------------'
printf 'Informe Markdown: %s\n' "$MD"
printf 'XML JUnit:        %s\n' "$XML"
printf 'Salida cruda:     %s\n' "$RAW"
printf 'Código de salida: %s\n' "$PEST_STATUS"
printf '%s\n' '----------------------------------------'

exit "$PEST_STATUS"
