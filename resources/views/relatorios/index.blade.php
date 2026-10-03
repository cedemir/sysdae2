<x-relatorios.pagina
    titulo="Relatórios"
    subtitulo="Escolha o relatório e, se quiser, filtre por aluno e por período."
    :caminho="[route('relatorios.index') => 'Relatórios', 'Gerar']"
>
    <x-filament::section>
        <form id="formulario" method="GET" action="{{ route('relatorios.gerar') }}" class="sd-rel-form">
            <div>
                <label for="tipo" class="sd-rel-rotulo">Relatório</label>
                <x-filament::input.wrapper :valid="! $errors->has('tipo')">
                    <x-filament::input.select id="tipo" name="tipo" required>
                        @foreach ($tipos as $chave => $tipo)
                            <option value="{{ $chave }}"
                                    data-usa="{{ implode(',', $tipo['usa']) }}"
                                    data-exige="{{ implode(',', $tipo['exige']) }}"
                                    @selected(old('tipo') === $chave)>{{ $tipo['rotulo'] }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
                @error('tipo') <div class="sd-rel-erro">{{ $message }}</div> @enderror
            </div>

            <div id="campo-apto" class="oculto">
                <label for="apto" class="sd-rel-rotulo">Número do apartamento</label>
                <x-filament::input.wrapper :valid="! $errors->has('apto')">
                    <x-filament::input id="apto" name="apto" :value="old('apto')" maxlength="10" placeholder="Exemplo: 301" />
                </x-filament::input.wrapper>
                @error('apto') <div class="sd-rel-erro">{{ $message }}</div> @enderror
            </div>

            <div id="campo-aluno" class="oculto">
                <label for="busca" class="sd-rel-rotulo">Aluno <small id="aluno-opcional">(opcional)</small></label>
                <div class="sd-rel-campo">
                    <x-filament::input.wrapper :valid="! $errors->has('cpf')">
                        <x-filament::input id="busca" name="busca" type="text" autocomplete="off" maxlength="150"
                                           placeholder="Digite o nome ou o CPF" :value="old('busca')" />
                    </x-filament::input.wrapper>
                    <input id="cpf" name="cpf" type="hidden" value="{{ old('cpf') }}">
                    <ul id="sugestoes" class="sd-rel-sugestoes oculto"></ul>
                </div>
                <div id="escolhido" class="sd-rel-escolhido"></div>
                <div id="erro-aluno" class="sd-rel-erro"></div>
                @error('cpf') <div class="sd-rel-erro">{{ $message }}</div> @enderror
            </div>

            <div id="campo-periodo" class="sd-rel-linha oculto">
                <div>
                    <label for="de" class="sd-rel-rotulo">De <small>(opcional)</small></label>
                    <x-filament::input.wrapper :valid="! $errors->has('de')">
                        <x-filament::input id="de" name="de" type="date" :value="old('de')" />
                    </x-filament::input.wrapper>
                    @error('de') <div class="sd-rel-erro">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label for="ate" class="sd-rel-rotulo">Até <small>(opcional)</small></label>
                    <x-filament::input.wrapper :valid="! $errors->has('ate')">
                        <x-filament::input id="ate" name="ate" type="date" :value="old('ate')" />
                    </x-filament::input.wrapper>
                    @error('ate') <div class="sd-rel-erro">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="sd-rel-acoes">
                <x-filament::button type="submit" name="formato" value="tela" icon="heroicon-o-eye">
                    Ver na tela
                </x-filament::button>
                <x-filament::button type="submit" name="formato" value="pdf" formtarget="_blank" color="gray" icon="heroicon-o-arrow-down-tray">
                    Baixar PDF
                </x-filament::button>
            </div>
        </form>
    </x-filament::section>

    <script>
        const urlBusca = @json(route('relatorios.alunos'));
        const formulario = document.getElementById('formulario');
        const tipo = document.getElementById('tipo');
        const busca = document.getElementById('busca');
        const cpf = document.getElementById('cpf');
        const lista = document.getElementById('sugestoes');
        const escolhido = document.getElementById('escolhido');
        const erroAluno = document.getElementById('erro-aluno');
        let temporizador = null;
        let requisicao = 0;

        function atualizar() {
            const opcao = tipo.options[tipo.selectedIndex];
            const usa = (opcao.dataset.usa || '').split(',');
            const exige = (opcao.dataset.exige || '').split(',');
            document.getElementById('campo-apto').classList.toggle('oculto', !usa.includes('apto'));
            document.getElementById('campo-aluno').classList.toggle('oculto', !usa.includes('aluno'));
            document.getElementById('campo-periodo').classList.toggle('oculto', !usa.includes('periodo'));
            document.getElementById('aluno-opcional').textContent = exige.includes('aluno') ? '(obrigatório)' : '(opcional)';
        }

        function mostrarEscolhido(texto) {
            escolhido.replaceChildren();
            if (!texto) return;
            const span = document.createElement('span');
            span.textContent = 'Aluno selecionado: ' + texto;
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.textContent = 'Limpar';
            botao.className = 'fi-link fi-size-sm';
            botao.addEventListener('click', limparAluno);
            escolhido.append(span, botao);
        }

        function limparAluno() {
            cpf.value = '';
            busca.value = '';
            lista.classList.add('oculto');
            mostrarEscolhido('');
            busca.focus();
        }

        function escolher(aluno) {
            cpf.value = aluno.cpf;
            busca.value = aluno.nome;
            erroAluno.textContent = '';
            lista.classList.add('oculto');
            mostrarEscolhido(aluno.nome + ' (' + aluno.cpf_formatado + ')');
        }

        async function buscar(texto) {
            const minha = ++requisicao;
            try {
                const resposta = await fetch(urlBusca + '?q=' + encodeURIComponent(texto), {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!resposta.ok) throw new Error('falha');
                const alunos = await resposta.json();
                if (minha !== requisicao) return; // chegou uma resposta mais nova

                lista.replaceChildren();
                if (alunos.length === 0) {
                    const vazio = document.createElement('li');
                    vazio.textContent = 'Nenhum aluno encontrado.';
                    lista.appendChild(vazio);
                } else {
                    alunos.forEach(aluno => {
                        const item = document.createElement('li');
                        item.textContent = aluno.nome + ' (' + aluno.cpf_formatado + ')';
                        item.addEventListener('mousedown', evento => { evento.preventDefault(); escolher(aluno); });
                        lista.appendChild(item);
                    });
                }
                lista.classList.remove('oculto');
            } catch (erro) {
                lista.classList.add('oculto');
            }
        }

        busca.addEventListener('input', () => {
            const texto = busca.value.trim();
            const digitos = texto.replace(/\D/g, '');
            escolhido.replaceChildren();
            erroAluno.textContent = '';

            // CPF digitado por inteiro vale direto, sem precisar escolher na lista.
            cpf.value = (digitos.length === 11 && /^[\d.\-\s]+$/.test(texto)) ? digitos : '';

            clearTimeout(temporizador);
            if (texto.length < 2 || cpf.value) {
                lista.classList.add('oculto');
                return;
            }
            temporizador = setTimeout(() => buscar(texto), 250);
        });

        busca.addEventListener('blur', () => setTimeout(() => lista.classList.add('oculto'), 150));

        formulario.addEventListener('submit', evento => {
            const campoAluno = document.getElementById('campo-aluno');
            if (campoAluno.classList.contains('oculto')) return;
            if (busca.value.trim() !== '' && cpf.value === '') {
                evento.preventDefault();
                erroAluno.textContent = 'Escolha o aluno na lista ou digite o CPF completo.';
                busca.focus();
            }
        });

        tipo.addEventListener('change', atualizar);
        atualizar();
        if (cpf.value && busca.value) mostrarEscolhido(busca.value);
    </script>
</x-relatorios.pagina>
