# Project: ZABBIX Report - API Documentation
_**(Sistema de Relatórios Automatizados Integrado ao Zabbix)**_

## **🔍 Visão Geral**

A **ZABBIX Report** é uma aplicação que automatiza a geração e o envio de relatórios de monitoramento baseados em dados do **Zabbix**, oferecendo:  
✅ **Relatórios mensais** automatizados por e-mail.  
✅ **Assinatura digital** de documentos (certificado `.p12`).  
✅ **Gestão de clientes dedicados** com vinculação a hosts do Zabbix.  
✅ **API RESTful** para integração com outras ferramentas.

---

## **📂 Seções da API**

### **1\. 👥 User Management**

**Descrição**: Gestão de usuários da aplicação (administradores e usuários básicos).  
**Rotas Principais**:

- `GET /api/?op=user-search?search=...` → Busca usuario por nome, email, cpf, ou telefone.
    
- `GET /api/?op=user-get` → Busca dados de um usuario.
    
- `PUT /api/?op=user-new` → Cria um novo usuário.
    
- `PUT /api/?op=user-update`→ Atualiza um usuário.
    
- `PUT /api/?op=user-update-access` → Altera permissões (admin/básico).
    
- `DELETE /api/?op=user-delete` → Remove um usuário.
    
- `PUT /api/?op=user-send-recovery`→ Envia um e-mail de recuperação de senha para o usuário.
    

**Requisitos**:

- Acesso restrito a **administradores**.
    

---

### **2\. 🖥️ Host (Zabbix Integration)**

**Descrição**: Consulta de hosts, interfaces e métricas no Zabbix.  
**Rotas Principais**:

- `GET /api/?op=host-search?search=...` → Busca hosts por nome, ip, descrição, ou porta.
    
- `GET /api/?op=host-details` → Detalhes do host (CPU, RAM, redes).
    
- `POST /api/?op=host-get-interface-chart` → Dados para gráficos (tráfego, uso).
    
- `POST /api/?op=host-get-dedicate-list`→ Retorna lista de clientes associados ao host.
    
- `POST /api/?op=host-get-interface-list`→ Retorna lista de interfaces do host.
    
- `POST /api/?op=host-load-interface-history`→ Retorna o relatorio de um cliente em periodo especificado.
    

**Requisitos**:

- Token de API do Zabbix configurado.
    

---

### **3\. 📊 Dedicated Management**

**Descrição**: Vinculação de clientes a hosts do Zabbix e envio de relatórios.  
**Rotas Principais**:

- `PUT /api/?op=dedicated-save` → Associa um host a um cliente ou atualiza um existente.
    
- `GET /api/?op=dedicated-search` → Busca clientes por nome, endereço, ou e-mail.
    
- `GET /api/?op=dedicated-get`→ Retorna dados de um cliente.
    
- `DELETE /api/?op=dedicated-delete`→ Deleta um cliente.
    
- `POST /api/?op=dedicated-sent-report` → Gera e envia um relatório por e-mail.
    

---

### **4\. ⚙️ Config Management**

**Descrição**: Configurações da aplicação (SMTP, banco de dados, assinatura digital).  
**Rotas Principais**:

- `GET /api/?op=config-search&search=...` → Busca as configurações.
    
- `POST /api/?op=config-save` → Salva configurações.
    
- `TEST /api/?op=config-test-smtp` → Testa conexão com servidor SMTP.
    
- `TEST /api/?op=config-test-db` → Testa conexão com o banco de dados.
    

**Requisitos**:

- Acesso restrito a **administradores**.
# 📁 Collection: User Management 
 


## End-point: User Search
### Method: GET
>```
>/api/?op=user-search
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|sort|string|coluna para ordenação|
|order|string|ordem(asc,desc)|
|page|int|pagina a ser retornada|
|search|string|nome, e-mail, documento, ou telefone|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User Get
### Method: GET
>```
>/api/?op=user-get
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|id|int|Id do usuario|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User New
### Method: PUT
>```
>/api/?op=user-new
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|firstName|string|primeiro nome|
|lastName|string|sobrenome|
|email|string|e-mail do usuario|
|access_level|basic ou admin|nivel de acesso|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User Update
### Method: PUT
>```
>/api/?op=user-update
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|idUser|int|id do usuario|
|firstName|string|primeiro nome|
|lastName|string|sobrenome|
|email|string|e-mail do usuario|
|access_level|basic|nivel de acesso|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User Update Access Level
### Method: PUT
>```
>/api/?op=user-update-access
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|idUser|int|id do usuario|
|accessLevel|basic ou admin|nivel de acesso(basic, admin)|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User Send Recovery Link
### Method: PUT
>```
>/api/?op=user-send-recovery
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|idUser|int|id do usuario|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: User Delete
### Method: DELETE
>```
>/api/?op=user-delete
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|idUser|int|id do usuario|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
# 📁 Collection: Host Management 
 


## End-point: Host Search
### Method: GET
>```
>/api/?op=host-search
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|sort|string|coluna para ordenação|
|order|string|ordem(asc, desc)|
|page|int|pagina a ser retornada|
|search|string|nome, descrição, ip, ou porta|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Host Details
### Method: GET
>```
>/api/?op=host-details
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Host Get Interface List
### Method: POST
>```
>/api/?op=host-get-interface-list
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|




⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Host Get Dedicated List
### Method: POST
>```
>/api/?op=host-get-dedicated-list
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Host Get Interface Chart
### Method: POST
>```
>/api/?op=host-get-interface-chart
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|
|graphid|int|Id do grafico do host, o grafico com este Id será retornado mesmo se estiver vinculado a um dedicado|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Host Load Interface History
### Method: POST
>```
>/api/?op=host-load-interface-history
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|
|graphid|int|Id do grafico a ser usado|
|time_from|timestamp|timestamp da data incial|
|time_till|timestamp|timestamp da data final|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
# 📁 Collection: Dedicated Management 
 


## End-point: Dedicated Delete
### Method: DELETE
>```
>/api/?op=dedicated-delete
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|id|int|Id do cliente dedicado|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Dedicated Save
### Method: PUT
>```
>/api/?op=dedicated-save
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|
|graphid|int|Id do grafico do cliente|
|name|string|Nome do cliente dedicado|
|email|string|email para contatar o cliente|
|date_send_mail|int|data para envio de relatorio|
|auto_send|bool|determina se o envio será automatico ou não|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Dedicated Get
### Method: GET
>```
>/api/?op=dedicated-details
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|hostid|int|Id do host no zabbix|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Dedicated Search
### Method: GET
>```
>/api/?op=dedicater-search
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|sort|string|coluna para ordenação|
|order|string|ordem(asc ou desc)|
|page|int|pagina a ser retornada|
|search|string|nome, e-mail, ou endereço|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Dedicated Sent Report
### Method: POST
>```
>/api/?op=dedicated-sent-report
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|dedicatedId|int|Id do cliente|
|time_from|timestamp|timestamp da data inicial|
|time_till|timestamp|timestamp da data final|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
# 📁 Collection: Config Management 
 


## End-point: Config Get
### Method: GET
>```
>/api/?op=config-get
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Config Save
### Method: POST
>```
>/api/?op=config-save
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|config|json|json contendo a nova configuação|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Config Test DB
### Method: TEST
>```
>/api/?op=config-test-db
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|database|json|json contendo os dado para teste(dbHost,dbName,dbUserName,dbUserPassword)|



⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃

## End-point: Config Test SMTP
### Method: TEST
>```
>/api/?op=config-test-smtp
>```
### Headers

|Content-Type|Value|
|---|---|
|x-api-key|8f0816addea478c4e577b3b5b19a9629d4f7705f3930c8390307526a9a9c49da|


### Query Params

|Parametro|Valor|Descrição|
|---|---|---|
|smtp|json|json contendo os dado para teste(mailHost,mailPort,mailUsername,mailUserPassword)|

⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃ ⁃
_________________________________________________
Powered By: [Eduardo M. Ribeiro da Silva](https://github.com/EduardoMRS)