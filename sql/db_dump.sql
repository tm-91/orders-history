-- --------------------------------------------------------

--
-- Table structure for table `orders_history`
--

CREATE TABLE IF NOT EXISTS `orders_history` (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `shop_id` int(10) unsigned DEFAULT NULL,
  `order_id` int(10) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `added` blob,
  `edited` blob,
  `removed` blob,
  PRIMARY KEY (`id`),
  KEY `fk_orders_history` (`shop_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders_history`
--
ALTER TABLE `orders_history`
  ADD CONSTRAINT `fk_orders_history` FOREIGN KEY (`shop_id`) REFERENCES `shops` (`id`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
-- --------------------------------------------------------

--
-- Table structure for table `orders_current_state`
--

CREATE TABLE IF NOT EXISTS `orders_current_state` (
  `shop_id` int(10) NOT NULL,
  `order_id` int(10) NOT NULL,
  `order_data` blob NOT NULL,
  CONSTRAINT PRIMARY KEY
shop_order (`shop_id`,`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `orders_statuses`
--

CREATE TABLE IF NOT EXISTS `orders_statuses` (
  `id` int(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `shippings`
--

CREATE TABLE IF NOT EXISTS `shippings` (
  `id` int(10) NOT NULL,
  `name` varchar(40) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;