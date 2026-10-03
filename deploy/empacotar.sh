#!/usr/bin/env bash
# Gera o pacote do SYSDAE para levar ao servidor de produção.
# Rode no computador de desenvolvimento, na raiz do projeto:
#   bash deploy/empacotar.sh              (roda os testes antes)
#   bash deploy/empacotar.sh --sem-testes
set -euo pipefail

RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
cd "$RAIZ"

if [[ "${1:-}" != "--sem-testes" ]]; then
    echo ">> Rodando os testes (o pacote só é gerado se todos passarem)..."
    php artisan test
fi

mkdir -p dist
PACOTE="dist/sysdae-$(date +%Y%m%d-%H%M).tar.gz"

# Fica de fora: configuração e dados da máquina local, dependências (o servidor instala),
# arquivos de desenvolvimento e os scripts usados para montar o sistema.
tar -czf "$PACOTE" \
    --exclude='./.env' \
    --exclude='./.env.backup' \
    --exclude='./.env.production' \
    --exclude='./vendor' \
    --exclude='./node_modules' \
    --exclude='./storage' \
    --exclude='./bootstrap/cache/*.php' \
    --exclude='./public/storage' \
    --exclude='./public/hot' \
    --exclude='./tests' \
    --exclude='./dist' \
    --exclude='./.git' \
    --exclude='./.claude' \
    --exclude='./.idea' \
    --exclude='./.vscode' \
    --exclude='./.mcp.json' \
    --exclude='./.phpunit.cache' \
    --exclude='./.phpunit.result.cache' \
    --exclude='./CLAUDE.md' \
    --exclude='./AGENTS.md' \
    --exclude='./boost.json' \
    --exclude='./Guia*.pdf' \
    --exclude='./etapa*.php' \
    --exclude='./ajustar_*.php' \
    --exclude='./corrigir_*.php' \
    --exclude='./setup_sysdae*.php' \
    --exclude='*.bak' \
    --exclude='*.novo.php' \
    --transform='s,^\./,sysdae/,' \
    .

echo
echo ">> Pacote gerado: $PACOTE ($(du -h "$PACOTE" | cut -f1))"
echo
echo "Próximos passos:"
echo "  scp $PACOTE usuario@servidor:~/"
echo "  ssh usuario@servidor"
echo "  tar -xzf $(basename "$PACOTE") && cd sysdae"
echo "  sudo bash deploy/instalar.sh      # primeira instalação"
echo "  sudo bash deploy/atualizar.sh     # nova versão de um sistema já instalado"
