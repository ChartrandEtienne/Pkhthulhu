Pkhthulhu
=========

It's a parser combinator lib in PHP. Fight me

The name is inspired by this legendary StackOverflow answer;
http://stackoverflow.com/questions/1732348/regex-match-open-tags-except-xhtml-self-contained-tags/1732454#1732454

This library is what happens when the brain-twisting nest of eldritch regex horror is seriously tackled, with the help of a few Haskell research papers, the best monad tutorial ever, and a blogpost about Scala, but still within the dark realm of the neverending folly of humankind that PHP is. 

See, XML has a pretty sane and understandable syntax; really, one of the simplest around; minus the closing tag verification issue, and minus the semantic f... uh, issue of namespaces, it's almost as limpid as S-expressions. 

Regexes just can't do trees, and XML is a tree. 

So I decided that it'd be fun to actually write a kinda sort of extension to regex that can actually parse (X)HTML. 
