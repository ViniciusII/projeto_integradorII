document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const themeToggleIcon = document.getElementById('theme-toggle-icon');
    const currentTheme = localStorage.getItem('theme');

    // Função para aplicar o tema no carregamento da página
    function applyTheme(theme) {
        if (theme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeToggleIcon) themeToggleIcon.className = 'fas fa-sun'; // Ícone de sol
        } else {
            document.body.classList.remove('dark-mode');
            if (themeToggleIcon) themeToggleIcon.className = 'fas fa-moon'; // Ícone de lua
        }
    }

    // Aplica o tema salvo ao carregar a página
    if (currentTheme) {
        applyTheme(currentTheme);
    }

    // Listener para o clique no botão
    if (themeToggleBtn) {
        themeToggleBtn.addEventListener('click', () => {
            let theme;
            // Alterna a classe e define o novo tema
            if (document.body.classList.contains('dark-mode')) {
                document.body.classList.remove('dark-mode');
                theme = 'light';
            } else {
                document.body.classList.add('dark-mode');
                theme = 'dark';
            }
            
            // Salva a nova preferência no localStorage
            localStorage.setItem('theme', theme);

            // Atualiza o ícone
            applyTheme(theme);
        });
    }
});
