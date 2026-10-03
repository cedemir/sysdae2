// Desliga a validação nativa do navegador (que aparece no idioma do navegador, ex.: "Please fill out this field.").
// Assim quem valida é o Laravel, com as mensagens em português de lang/pt_BR/validation.php.
(function () {
    function desligarValidacaoNativa(raiz) {
        raiz.querySelectorAll('form:not([novalidate])').forEach(function (form) {
            form.noValidate = true;
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        desligarValidacaoNativa(document);

        // Formulários que surgem depois (modais, navegação do Livewire).
        new MutationObserver(function () {
            desligarValidacaoNativa(document);
        }).observe(document.body, { childList: true, subtree: true });
    });
})();
