<?php
	$XML_NAMESPACE_URI_INDEX = 0;
	
	$XML_CODEPAGE = 'UTF-8';
	$XML_VERSION = '1.0';

	/*
	 * Преобразует значение в кодировку UTF-8.
	 * @var $value value - значение
	 * return value OR boolean
	 */
	function WIN1251ToUTF8($value)
	{
		return iconv('Windows-1251', 'UTF-8', $value);
	}

	/*
	 * Преобразует значение в кодировку Windows-1251.
	 * @var $value value - значение
	 * return value OR boolean
	 */
	function UTF8ToWIN1251($value)
	{
		return iconv('UTF-8', 'Windows-1251', $value);
	}	

	/*
	 * Преобразует значение одной кодирвоки в другую.
	 * @var $value value - значение
	 * @var $from string - кодировка с какой преобразует
	 * @var $to string - кодировка в какую преобразуют
	 * return value OR boolean
	 */
	function FormatXMLCodePage($value, $from, $to)
	{
		return ($from != $to) ? iconv($from, $to, $value) : $value;
	}		

	/*
	 * Разбиваем информацию о пространстве имен
	 * (название пространства имен и значение)
	 * и передаем их по указателю во входные параметры.
	 * @var $value array() - переменная, хранящая информацию о пространстве имен
	 * @var $namespace_name string - название пространства имен
	 * @var $namespace_uri string - значение пространства имен
	 */
	function GetXMLNamespace($value, &$namespace_name, &$namespace_uri)
	{
		if (is_array($value))
		{
			foreach($value as $namespace_name => $namespace_uri) 
			{
			   break;
			}
		}else{
		   $namespace_name = '';
		   $namespace_uri = $value;			
		}	
	}

	/*
	 * Возвращает информацию о названии пространства имен.
	 * @var $value string - переменная, хранящая информацию о пространстве имен
	 * return string
	 */
	function GetXMLNamespaceName($value)
	{
		GetXMLNamespace($value, $namespace_name, $namespace_uri);
		return $namespace_name;
	}

	/*
	 * Возвращает значения пространства имен.
	 * @var $value string - переменная, хранящая информацию о пространстве имен
	 * return string
	 */
	function GetXMLNamespaceURI($value)
	{
		GetXMLNamespace($value, $namespace_name, $namespace_uri);
		return $namespace_uri;
	}	

	/*
	 * Возвращает кодировку XML документа.
	 * @var $xml DOMCoument - XML-документ
	 * return string
	 */
	function GetXMLEncoding(&$xml)
	{
		global $XML_CODEPAGE;
		
		if (!is_object($xml))
		{
			return $XML_CODEPAGE;
		}				
		
		$result = (isset($xml->ownerDocument)) ? $xml->ownerDocument->encoding : $xml->encoding;
		
		return ($result != '') ? $result : $XML_CODEPAGE;
	}

	/*
	 * Возвращает созданный XML элемент в зависимости от входных параметров.
	 * Элемент может быть создан, как с пространством имен, так и без него.
	 * @var $xml object - XML-документ
	 * @var $name string - название тега
	 * @var $value value - значение тега
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return object
	 */
	function CreateXMLNode(&$xml, $name, $value = '', $namespace = null, $codepage = 'UTF-8')
	{	
		if (!is_object($xml) or $name == '')
		{
			return false;
		}				
		
		$result = false;
		
		$encoding = GetXMLEncoding($xml);
		
		$value = FormatXMLCodePage($value, $codepage, $encoding);
		
		if (isset($namespace))
		{
			GetXMLNamespace($namespace, $namespace_name, $namespace_uri);

			if ($namespace_name != '')
			{		
				$name = $namespace_name.':'.$name;
			}
			
			$namespace_uri = FormatXMLCodePage($namespace_uri, $codepage, $encoding);
			$name = FormatXMLCodePage($name, $codepage, $encoding);
			
			if ($value != '')
			{
				$result = $xml->createElementNS($namespace_uri, $name, $value);	
			}else{
				$result = $xml->createElementNS($namespace_uri, $name);	
			}
		}else{
			$name = FormatXMLCodePage($name, $codepage, $encoding);
			
			if ($value != '')
			{
				$result = $xml->createElement($name, $value);			
			}else{
				$result = $xml->createElement($name);		
			}		
		}
		
		return $result;		
	}		

	/*
	 * Копирует дерево тегов одного тега в другой.
	 * Применимо, как в одном XML-документе, так и в нескольких.
	 * Если происходит копия тега внутри одного XML документа, то копируется
	 * только один тег (первый, даже если их несколько) из указанного тега-ресурса
	 * в указанный тег-назначения.
	 * @var $xml_src object - тег-ресурс
	 * @var $xml_dest object - тег-назначения
	 * @var $recursion boolean - ПОКА НЕ ПОНЯТНО
	 * return boolean
	 */
	function CopyXMLNode($xml_src, $xml_dest, $recursion = true)
	{
		if (!is_object($xml_src) or !is_object($xml_dest))
		{
			return false;
		}		
		
		$xml = (isset($xml_dest->ownerDocument)) ? $xml_dest->ownerDocument : $xml_dest;
		
		foreach ($xml_src->childNodes as $node)
		{	
			if ($recursion)
			{
				$res = $xml_dest->appendChild($xml->importNode($node, false));
				if ($res !== false)
				{
					CopyXMLNode($node, $res, $recursion);
				}
			}else{
				$xml_dest->appendChild($xml->importNode($node, true));
			}
		}
		
		return true;
	}
	

	/*
	 * Добавляет новый дочерный тег в указанный тег или XML-документ.
	 * @var $xml object - XML-документ или XMl-тег
	 * @var $name string - название для будущего тега
	 * @var $value string - значение будущего тега
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return object
	 */
	function AddXMLChild(&$xml, $name, $value = '', $namespace = null, $codepage = 'UTF-8')
	{
		if (!is_object($xml))
		{
			return false;
		}			
		
		$owner = (isset($xml->ownerDocument)) ? $xml->ownerDocument : $xml;
		
		return $xml->appendChild(CreateXMLNode($owner, $name, $value, $namespace, $codepage));			
	}

	/*
	 * Удаляет XML-тег/дерево тегов.
	 * @var $xml object - тег для удаления
	 * return boolean
	 */
	function DelXMLChild(&$xml)
	{
		if (!is_object($xml))
		{
			return false;
		}			
		
		if (!is_object($xml->parentNode))
		{
			return false;
		}
		
		$xml->parentNode->removeChild($xml);			
		
		return true;
	}	

	/*
	 * Возвращает тег в указанном во входном параметре XML-документе по названию
	 * тега в указанном пространстве имен (если пространство имен не указано, то
	 * поиск будет осуществляеться только по названию тега).
	 * @var $xml object - XML-документ
	 * @var $name string - название тега
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * retrun object OR boolean
	 */
	function GetXMLNode(&$xml, $name, $namespace = null, $codepage = 'UTF-8')
	{
		if (!is_object($xml))
		{
			return false;
		}		
		
		$result = false;
	
		$encoding = GetXMLEncoding($xml);
	
		$name = FormatXMLCodePage($name, $codepage, $encoding);
		
		if (isset($namespace))
		{
			foreach ($xml->getElementsByTagNameNS(FormatXMLCodePage(GetXMLNamespaceURI($namespace), $codepage, $encoding), $name) as $element)
			{
				$result = $element;
				break;
			}		
		}else{
			foreach ($xml->getElementsByTagName($name) as $element)
			{
				$result = $element;
				break;
			}			
		}
		
		return $result;
	}

	/*
	 * Возвращает значение тега. Тег может быть указан в первом входном параметре
	 * (при этом второй входной параметр должен быть пустым или его просто можно не указывать).
	 * Так же тег может быть указан во втором входном параметре (при этом первым параметром
	 * указывается его корневой тег (если корневого тега нет, то необходимо указать его в первом
	 * входном параметре, как описывалось ранее)).
	 * @var $xml object - тег
	 * @var $name string - название тега
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return string OR boolean
	 */
	function GetXMLValue(&$xml, $name = null, $namespace = null, $codepage = 'UTF-8')
	{	
		if (!is_object($xml))
		{
			return false;
		}		
		
		if ($name == null)
		{
			$node = $xml;
		}else{
			$node = GetXMLNode($xml, $name, $namespace);
			if ($node === false)
			{
				return false;
			}
		}

		return FormatXMLCodePage($node->nodeValue, GetXMLEncoding($xml), $codepage);
	}

	/*
	 * Устанавливает значение тега. Тег может быть указан в первом входном параметре
	 * (при этом второй входной параметр должен быть пустым или его просто можно не указывать).
	 * Так же тег может быть указан во втором входном параметре (при этом первым параметром
	 * указывается его корневой тег (если корневого тега нет, то необходимо указать его в первом
	 * входном параметре, как описывалось ранее)).
	 * @var $xml object - тег
	 * @var $name string - название тега
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return boolean
	 */
	function SetXMLValue(&$xml, $name, $value, $namespace = null, $codepage = 'UTF-8')
	{		
		if (!is_object($xml))
		{
			return false;
		}
		
		if ($name == null)
		{
			$node = $xml;
		}else{
			$node = GetXMLNode($xml, $name, $namespace);
			if ($node === false)
			{
				return false;
			}
		}		

		$node->nodeValue = FormatXMLCodePage($value, $codepage, GetXMLEncoding($xml));
		
		return true;		
	}	

	/*
	 * Возвращает значение атрибута в указанном теге.
	 * @var $xml object - тег
	 * @var $name string - название атрибута
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return string OR boolean
	 */
	function GetXMLAttribute(&$xml, $name, $namespace = null, $codepage = 'UTF-8')
	{	
		if (!is_object($xml))
		{
			return false;
		}
		
		$result = false;
		
		$encoding = GetXMLEncoding($xml);
		
		$name = FormatXMLCodePage($name, $codepage, $encoding);
		
		if (isset($namespace))
		{
			$namespace_uri = FormatXMLCodePage(GetXMLNamespaceURI($namespace), $codepage, $encoding);
			
			if ($xml->hasAttributeNS($namespace_uri, $name))
			{
				$result = FormatXMLCodePage($xml->getAttributeNS($namespace_uri, $name), $encoding, $codepage);
			}
		}else{
			if ($xml->hasAttribute($name))
			{
				$result = FormatXMLCodePage($xml->getAttribute($name), $encoding, $codepage);
			}		
		}
		
		return $result;
	}

	/*
	 * Устанавливает значения атрибута для тега.
	 * @var $xml object - тег
	 * @var $name string - название атрибута
	 * @var $value string - значение атрибута
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return object
	 */
	function SetXMLAttribute(&$xml, $name, $value, $namespace = null, $codepage = 'UTF-8')
	{
		if (!is_object($xml))
		{
			return false;
		}
		
		$result = false;
		
		$encoding = GetXMLEncoding($xml);
		
		$value = FormatXMLCodePage($value, $codepage, $encoding);
		
		if (isset($namespace))
		{
			GetXMLNamespace($namespace, $namespace_name, $namespace_uri);	
		
			if ($namespace_name != '')
			{		
				$name = $namespace_name.':'.$name;
			}		
		
			$result = $xml->setAttributeNS(FormatXMLCodePage($namespace_uri, $codepage, $encoding), FormatXMLCodePage($name, $codepage, $encoding), $value);
		}else{
			$result = $xml->setAttribute(FormatXMLCodePage($name, $codepage, $encoding), $value);
		}
		
		return $result;
	}

	/*
	 * Удаляет атрибут в указанном теге.
	 * @var $xml object - тег
	 * @var $name string - название атрибута
	 * @var $namespace array() - пространство имен
	 * @var $codepage string - кодировка
	 * return boolean
	 */
	function DelXMLAttribute(&$xml, $name, $namespace = null, $codepage = 'UTF-8')
	{
		global $XML_NAMESPACE_URI_INDEX;
		
		if (!is_object($xml))
		{
			return false;
		}		
		
		$result = false;
		
		$encoding = GetXMLEncoding($xml);
		
		$name = FormatXMLCodePage($name, $codepage, $encoding);
		
		if (isset($namespace))
		{
			$namespace_uri = FormatXMLCodePage(GetXMLNamespaceURI($namespace), $codepage, $encoding);
			
			if ($xml->hasAttributeNS($namespace_uri, $name))
			{
				$result = $xml->removeAttributeNS($namespace_uri, $name);
			}
		}else{
			if ($xml->hasAttribute($name))
			{
				$result = $xml->removeAttribute($name);
			}				
		}
		
		return $result;
	}