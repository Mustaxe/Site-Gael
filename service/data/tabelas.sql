-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_cases`
--

CREATE TABLE IF NOT EXISTS `tbl_cases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `imagem_thumb` int(11) NOT NULL,
  `imagem_thumb_over` int(11) NOT NULL,
  `imagem_integra` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `ativo` char(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_contatos`
--

CREATE TABLE IF NOT EXISTS `tbl_contatos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `nome` varchar(150) NOT NULL,
  `descricao` varchar(255) NOT NULL,
  `data_envio` datetime NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_destaques`
--

CREATE TABLE IF NOT EXISTS `tbl_destaques` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `ativo` char(1) NOT NULL,
  `imagem` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_projetos`
--

CREATE TABLE IF NOT EXISTS `tbl_projetos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `contato_email` varchar(255) NOT NULL,
  `contato_fone` varchar(20) NOT NULL,
  `contato_endereco` varchar(255) NOT NULL,
  `projeto` varchar(100) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `video` varchar(500) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Extraindo dados da tabela `tbl_projetos`
--

INSERT INTO `tbl_projetos` (`id`, `contato_email`, `contato_fone`, `contato_endereco`, `projeto`, `status`, `video`) VALUES
(1, 'info@crazygael.com.br', '+55 11 3033-0033', 'Av. Roque Petroni Jr, nº 999, 7º andar', 'Gael', 1, 'http://linkvideo');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_log`
--

CREATE TABLE IF NOT EXISTS `tbl_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `msg` varchar(1000) NOT NULL,
  `data` datetime NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_rota`
--

CREATE TABLE IF NOT EXISTS `tbl_rota` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=8;

--
-- Extraindo dados da tabela `tbl_rota`
--

INSERT INTO `tbl_rota` (`id`, `nome`, `status`) VALUES
(1, '/admin/cases', 1);
(2, '/admin/contatos', 1);
(3, '/admin/destaques', 1);
(4, '/admin/grupos', 1);
(5, '/admin/projetos', 1);
(6, '/admin/rotas', 1);
(7, '/admin/usuarios', 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_usuario`
--

CREATE TABLE IF NOT EXISTS `tbl_usuario` (
  `id` int(15) NOT NULL AUTO_INCREMENT,
  `email` varchar(500) NOT NULL,
  `emailCanonical` varchar(500) NOT NULL,
  `hashSenha` varchar(100) DEFAULT NULL,
  `grupos` text,
  `ultimoLogin` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  `facebookId` bigint(20) DEFAULT NULL,
  `facebookLink` varchar(255) DEFAULT NULL,
  `nome` varchar(255) DEFAULT NULL,
  `chaveTempo` int(11) DEFAULT NULL,
  `chave` varchar(40) DEFAULT NULL,
  `facebookAccessToken` varchar(500) DEFAULT NULL,
  `tokenConfirmacao` VARCHAR(150) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`(300))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

--
-- Diminuido o tamanho da unique key do e-mail (só funciona tamanho maior com charset=latin1): 
-- http://bugs.mysql.com/bug.php?id=6604
-- http://bugs.mysql.com/bug.php?id=4541
--

--
-- Extraindo dados da tabela `tbl_usuario`
--

INSERT INTO `tbl_usuario` (`id`, `email`, `emailCanonical`, `hashSenha`, `grupos`, `ultimoLogin`, `status`, `facebookId`, `facebookLink`, `nome`, `chaveTempo`, `chave`) VALUES
(1, 'user@example.com', 'user@example.com', '$2a$08$92112bbfc198068d81c0fuc.BMKjMsrIQZj8csXeC.FEmlxb1SqGy', '1', '2014-03-12 00:09:06', 1, NULL, NULL, NULL, 1394582946, 'YFxOH9lE7svmoIp0raL0Ybs3FU4Eqgfq30uRV8np');

-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_usuario_grupo`
--

CREATE TABLE IF NOT EXISTS `tbl_usuario_grupo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `grupo` varchar(255) NOT NULL,
  `rotas` text NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2;

--
-- Extraindo dados da tabela `tbl_usuario_grupo`
--

INSERT INTO `tbl_usuario_grupo` (`id`, `grupo`, `rotas`, `status`) VALUES
(1, 'admin', '1,2,3,4,5,6,7', 1);


-- --------------------------------------------------------

--
-- Estrutura da tabela `tbl_arquivo`
--

CREATE TABLE IF NOT EXISTS `tbl_arquivo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `checksum` varchar(255) NOT NULL,
  `modificado` datetime NOT NULL,
  `tamanho` int(11) NOT NULL,
  `extensao` varchar(5) NOT NULL,
  `tipo` varchar(255) NOT NULL,
  `nomeOriginal` varchar(255) NOT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;
