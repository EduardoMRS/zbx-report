# ZABBIX Report

Sistema automatizado de relatórios para dados do Zabbix, com geração de PDF, envio por e-mail e assinatura digital.

![Interface Responsiva](https://img.shields.io/badge/Responsivo-Sim-green) 
![API Zabbix](https://img.shields.io/badge/API%20Zabbix-v7-blue)

## ✨ Funcionalidades Principais

- **Relatórios em PDF Automatizados**
  - Modelos com timbre da empresa
  - Gráficos dinâmicos (largura de banda/latência)
  - Assinatura digital (.p12)
  - Nome padrão: `RELATORIO_CLIENTE_MES_ANO.pdf`

- **Painel Web**
  - Interface responsiva
  - Gerenciamento de clientes
  - Configuração de templates de e-mail

- **Automação**
  - Envio agendado (cron)
  - Logs de erro e notificações
  - API para integrações externas

## 📚 Documentação

- [Documentação da API](guide_api.md) - Referência completa dos endpoints
- [Tratamento de Erros](docs/errors.md) - Solução de problemas comuns

## 🚀 Instalação Rápida (Linux/Ubuntu)

```bash
wget https://raw.githubusercontent.com/ThomasJPF/Envia-Relatorio-Zabbix-UNI/main/start.sh && \
chmod +x start.sh && \
sudo ./start.sh
```

## 🔧 Requisitos
- PHP 8.0+
- Acesso à API do Zabbix 7
- Credenciais SMTP para envio de e-mails
- (Opcional) Certificado digital para assinatura

## 📦 Dependências
- text
- jpgraph/jpgraph     - Geração de gráficos
- setasign/fpdf       - Criação de PDF
- setasign/fpdi       - Manipulação de templates PDF

## 🤝 Como Contribuir
Contribuições são bem-vindas! Para mudanças grandes, abra uma issue primeiro.