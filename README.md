# Plugin Lista de Imóveis para WordPress

![Versão do WordPress](https://img.shields.io/badge/WordPress-6.0%2B-blue.svg)
![Requer PHP](https://img.shields.io/badge/PHP-7.4%2B-blueviolet.svg)
![Licença](https://img.shields.io/badge/Licen%C3%A7a-GPLv2-green.svg)

Um plugin completo e robusto para criar e gerenciar um portal de imóveis dentro do WordPress, com integração profunda e profissional com o construtor de páginas Elementor.

## Sobre o Plugin

Este plugin transforma uma instalação padrão do WordPress em uma poderosa plataforma para imobiliárias e corretores. Ele utiliza a arquitetura nativa do WordPress, criando um **Custom Post Type (CPT)** para "Imóveis" e **Taxonomias** personalizadas, garantindo máxima compatibilidade, performance e flexibilidade com o ecossistema WordPress.

## Funcionalidades Principais

- **Gestão de Imóveis via CPT:** Cadastre imóveis como um tipo de conteúdo nativo do WordPress, com uma interface familiar e poderosa.
- **Campos Customizados Completos:** Um formulário de cadastro com mais de 30 campos específicos para imóveis, incluindo dados de endereço, áreas, características e galeria de fotos.
- **Busca de Endereço com Google Maps:** Facilita o cadastro de endereços com um campo de autocomplete que preenche automaticamente rua, bairro, cidade, estado e CEP.
- **Integração Profunda com Elementor:**
    - **Categoria de Widgets Dedicada:** Todos os widgets do plugin ficam organizados em uma categoria própria no Elementor.
    - **4 Widgets Customizados:**
        - **Detalhes Personalizados:** Widget flexível com repetidor para montar uma ficha técnica como desejar.
        - **Informações Principais:** Bloco simples para dados-chave como referência e disponibilidade.
        - **Características:** Lista automática de todas as comodidades que o imóvel possui.
        - **Galeria de Fotos:** Exibe a galeria de imagens do imóvel.
    - **Tags Dinâmicas:** Puxe qualquer dado de um imóvel para dentro de qualquer widget do Elementor.
- **Templates Padrão Responsivos:** O plugin oferece templates de listagem e de página de detalhes que funcionam com qualquer tema, com opção de desativá-los.
- **API REST (CRUD) Segura:** Endpoints para Criar, Ler, Atualizar e Deletar imóveis, permitindo a integração com sistemas externos e aplicativos. Acesso restrito a usuários autenticados via Senhas de Aplicativo.
- **Filtros no Painel Admin:** Filtre sua lista de imóveis por Finalidade, Bairro e Faixa de Valor diretamente no painel do WordPress.

## Requisitos

- WordPress 6.0 ou superior
- PHP 7.4 ou superior
- Plugin Elementor (para usar as funcionalidades de integração)

## Instalação

1.  Faça o download do plugin (ou clone o repositório).
2.  Envie a pasta `lista-de-imoveis` para o diretório `/wp-content/plugins/` da sua instalação WordPress.
3.  Vá até o menu "Plugins" no painel do WordPress e ative o "Lista de Imóveis".
4.  Após a ativação, vá em **Configurações > Links Permanentes** e clique em "Salvar alterações" para garantir que as URLs dos imóveis funcionem corretamente.

## Configuração

Após a instalação, configure o plugin em **Imóveis > Configurações**:

1.  **Chave da API do Google Maps:** Insira sua chave de API para habilitar a busca de endereços no cadastro de imóveis.
2.  **Templates do Plugin:** Marque esta opção para usar os templates de exibição padrão do plugin. Desmarque se você for criar seus próprios layouts com o Elementor Theme Builder ou com arquivos de template no seu tema.

## Estrutura de Arquivos
```
.
├── includes/             # Classes principais (Helpers)
├── admin/                # Lógica do painel admin (Metabox)
├── elementor/            # Lógica da integração com Elementor
├── public/               # Assets públicos (CSS, JS)
├── templates/            # Templates padrão (archive e single)
└── lista-de-imoveis.php  # Arquivo principal (loader)
```

## Licença

Este plugin é distribuído sob a licença GPLv2 ou posterior.

---
*Este plugin foi desenvolvido em um processo colaborativo com a IA da Google.*