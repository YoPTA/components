<?php
	$XML_NAMESPACE_URI_INDEX = 0;
	
	$XML_CODEPAGE = 'UTF-8';
	$XML_VERSION = '1.0';
	
	function WIN1251ToUTF8($value)
	{
		return iconv('Windows-1251', 'UTF-8', $value);
	}	

	function UTF8ToWIN1251($value)
	{
		return iconv('UTF-8', 'Windows-1251', $value);
	}	
	
	function FormatXMLCodePage($value, $from, $to)
	{
		return ($from != $to) ? iconv($from, $to, $value) : $value;
	}		
	
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
	
	function GetXMLNamespaceName($value)
	{
		GetXMLNamespace($value, $namespace_name, $namespace_name);
		return $namespace_name;
	}		
	
	function GetXMLNamespaceURI($value)
	{
		GetXMLNamespace($value, $namespace_name, $namespace_uri);
		return $namespace_uri;
	}	
	
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
	
	function CreateXMLNode(&$xml, $name, $value = '', $namespace = null, $codepage = 'Windows-1251')
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
	

	function AddXMLChild(&$xml, $name, $value = '', $namespace = null, $codepage = 'Windows-1251')
	{
		if (!is_object($xml))
		{
			return false;
		}			
		
		$owner = (isset($xml->ownerDocument)) ? $xml->ownerDocument : $xml;
		
		return $xml->appendChild(CreateXMLNode($owner, $name, $value, $namespace, $codepage));			
	}
	
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

	function GetXMLNode(&$xml, $name, $namespace = null, $codepage = 'Windows-1251')
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
	
	function GetXMLValue(&$xml, $name = null, $namespace = null, $codepage = 'Windows-1251')
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
	
	function SetXMLValue(&$xml, $name, $value, $namespace = null, $codepage = 'Windows-1251')
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
	
	function GetXMLAttribute(&$xml, $name, $namespace = null, $codepage = 'Windows-1251')
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
	
	function SetXMLAttribute(&$xml, $name, $value, $namespace = null, $codepage = 'Windows-1251')
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
	
	function DelXMLAttribute(&$xml, $name, $namespace = null, $codepage = 'Windows-1251')
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