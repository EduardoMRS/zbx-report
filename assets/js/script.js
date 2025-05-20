/**
 * Copia texto para area de tranferencia
 * @param {string} text - Texto que será copiado
 * @param {string} text - Texto que será mostrado no alert
 */
function CopyToClipBoard(text, textAlert = null) {
    // Verifica se o navegador suporta a API Clipboard
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text)
            .then(() => {
                const alertMessage = textAlert || "Texto copiado para a área de transferência!";
                alert(alertMessage);
            })
            .catch((error) => {
                console.error("Erro ao copiar o texto:", error);
                alert("Erro ao copiar o texto. Tente novamente.");
            });
    } else {
        // Fallback para navegadores que não suportam a API Clipboard
        const textarea = document.createElement("textarea");
        textarea.value = text;
        document.body.appendChild(textarea);
        textarea.select();

        try {
            const successful = document.execCommand("copy");
            if (successful) {
                const alertMessage = textAlert || "Texto copiado para a área de transferência!";
                alert(alertMessage);
            } else {
                alert("Erro ao copiar o texto. Tente novamente.");
            }
        } catch (error) {
            console.error("Erro ao copiar o texto:", error);
            alert("Erro ao copiar o texto. Tente novamente.");
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

/**
 * Formata datas em JavaScript com múltiplos padrões
 * @param {string|Date} dateInput - Data como string ou objeto Date
 * @param {string} format - Formato desejado (ex: 'Y-m-d', 'd/m/Y H:i:s')
 * @return {string} - Data formatada
 */
function formatDate(format = 'Y-m-d', dateInput) {
    // Converter para objeto Date se for string
    const date = new Date(dateInput);

    // Verificar se a data é válida
    if (isNaN(date.getTime())) {
        throw new Error(`Data inválida fornecida '${dateInput}'`);
    }

    // Extrair componentes da data
    const components = {
        Y: date.getFullYear(),                          // Ano (4 dígitos)
        y: String(date.getFullYear()).slice(-2),        // Ano (2 dígitos)
        m: String(date.getMonth() + 1).padStart(2, '0'), // Mês (2 dígitos)
        n: date.getMonth() + 1,                         // Mês (sem zero)
        d: String(date.getDate()).padStart(2, '0'),     // Dia (2 dígitos)
        j: date.getDate(),                              // Dia (sem zero)
        H: String(date.getHours()).padStart(2, '0'),    // Hora (24h)
        h: String(date.getHours() % 12 || 12).padStart(2, '0'), // Hora (12h)
        i: String(date.getMinutes()).padStart(2, '0'),  // Minutos
        s: String(date.getSeconds()).padStart(2, '0'),  // Segundos
        a: date.getHours() < 12 ? 'am' : 'pm',          // am/pm
        A: date.getHours() < 12 ? 'AM' : 'PM'           // AM/PM
    };

    // Substituir os placeholders no formato
    return format.replace(/[YymdjnHisAa]/g, match => components[match]);
}


function troll() {
    const trollConfig = {
        image: "https://i.imgur.com/6tFSRFN.png",
        message: "Huuum...🙈 Parece que temos um curioso aqui! kkkk"
    };
    function activateTrollMode() {
        document.body.innerHTML = `
            <div style="text-align: center; margin-top: 50px; font-family: Arial;">
                <img src="${trollConfig.image}" width="200" style="border-radius: 50%">
                <h1>${trollConfig.message}</h1>
                <h5>${formatDate("H:i:s", new Date())}</h5>
                <p>Você achou que ia ver o código? Não vai ser tão fácil!</p>
            </div>
        `;

        setTimeout(() => { debugger; }, 500);
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'F12' ||
            (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'J' || e.key === 'C')) ||
            (e.ctrlKey && e.key === 'U')) {
            e.preventDefault();
            activateTrollMode();
        }
    });

    let isInspecting = false;
    document.addEventListener('contextmenu', (e) => {
        isInspecting = true;
        setTimeout(() => { isInspecting = false; }, 1000);
    });

    document.addEventListener('mousemove', (e) => {
        if (isInspecting && e.clientX > window.innerWidth - 200) {
            activateTrollMode();
        }
    });

    let isDevToolsOpen = false;
    setInterval(() => {
        const widthDiff = window.outerWidth - window.innerWidth;
        if ((widthDiff > 100 || window.outerWidth === 0) && !isDevToolsOpen) {
            isDevToolsOpen = true;
            activateTrollMode();
        }
    }, 500);
}


function maskCpf() {
    $(document).ready(function () {
        $('#document').mask('000.000.000-00');
    });
}
function maskPhone() {
    $(document).ready(function () {
        $('#phone').mask('(00)00000-0000');
        $('.phone').mask('(00)00000-0000');
    });
}