# ZABBIX Report

Sistema automatizado de relatórios com dados do Zabbix, com geração de PDF, envio por e-mail e assinatura digital.
## **🔍 Visão Geral**

Esta é uma aplicação que automatiza a geração e o envio de relatórios de monitoramento baseados em dados do **Zabbix**, oferecendo:  
✅ **Relatórios mensais** automatizados por e-mail.  
✅ **Assinatura digital** de documentos (certificado `.p12`).  
✅ **Gestão de clientes dedicados** com vinculação a hosts do Zabbix.  
✅ **API RESTful** para integração com outras ferramentas.

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

## 🌐 Pagina do Projeto
[Clique para visitar](https://deveduardomrs.pro/projetos/zbx-report/)

## 📚 Documentação

- [Documentação da API](guide_api.md) - Referência completa dos endpoints

## 🚀 Instalação Rápida (Linux/Ubuntu)

```bash
wget https://raw.githubusercontent.com/EduardoMRS/zbx-report/refs/heads/main/setup/start.sh && \
chmod +x start.sh && \
sudo ./start.sh
```

## 🔧 Requisitos
- PHP 8.0+
- Acesso à API do Zabbix 7
- Credenciais SMTP para envio de e-mails
- (Opcional) Certificado digital para assinatura

## 📦 Dependências
|Composer|Função|
|--|--|
|jpgraph/jpgraph|Geração de gráficos|
|setasign/fpdf|Criação de PDF|
|setasign/fpdi|Manipulação de templates PDF|
|setasign/fpdi-fpdf|Manipulação de gráficos e PDF|
|tecnickcom/tcpdf|Para adição de assinatura .P12 em PDF|
|phpmailer/phpmailer|Para envio de E-Mails|
|phpoffice/phpspreadsheet|Para Geração de Planilhas|

## 🤝 Como Contribuir
Contribuições são bem-vindas! Para mudanças grandes, abra uma issue primeiro.
