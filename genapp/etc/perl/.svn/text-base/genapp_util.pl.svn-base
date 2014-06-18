# subroutines for general usage

use JSON;
# use Switch;
# use Data::Dumper;

my $gap_version = '0.01';

%__json_scratch = {};

sub load_reserved_words {
    my $fh;
    my $f = "$gap/etc/reserved_words";
    return "$f not found, will not be applied" if !-e $f;
    open $fh, "$f";
    while ( my $l = <$fh> )
    {
        next if $l =~ /^\s*#/;
        chomp $l;
        $reserved_words{ $l }++ if length( $l );;
    }
    "";
}

sub show_state {
    my $ref   = $_[ 0 ];
    my $tag   = $_[ 1 ];
    my $depth = $_[ 2 ];
#    print "$tag: d $depth state of __json_scratch\{ $ref \}:\n";
    print "d $depth: $tag: size of array: " . scalar @{$__json_scratch{ $ref }} . "\n";
}

# $debug_rplc++;

sub json_expand_util {
    my $json  = $_[ 0 ];
    my $ref   = $_[ 1 ];
    my $tag   = $_[ 2 ];
    my $depth = $_[ 3 ];

    print '-'x80 . "\n" if $debug_rplc;
    print "d $depth json_expand util " . ref( $json ) . "\n" if $debug_rplc;
    print '-'x80 . "\n" if $debug_rplc;
    $$ref{ "skipinc" } = 0;

    if ( ref( $json ) eq 'HASH' )
    {
        # make 2 pass, one pass for non-refs, 2nd for arrays
        my @added;
        while ( my ($k, $v ) = each %$json )
        {
            if ( !ref( $v ) )
            {
                my $ntag = ( $tag ? "$tag:" : "" ) . $k;
                print '-'x60 . "\n" if $debug_rplc;
                show_state( $ref, "pre adding hash element '$ntag' '$v'", $depth ) if $debug_rplc;
                if ( $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $ntag } )
                {
                    $$ref{ "pos" }++;
                    %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} =
                        %{$__json_scratch{ $ref }[ $$ref{ "pos" } - 1 ]};
                    $$ref{ "skipinc" } = 0;
                }
                $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $ntag } = $v;
                push @added, $ntag;
                show_state( $ref, "post adding hash element '$ntag' '$v'", $depth ) if $debug_rplc;
            }
        }
        my $array_count = 0;
        while ( my ($k, $v ) = each %$json )
        {
            if ( ref( $v ) eq 'HASH' )
            {
                my $ntag = ( $tag ? "$tag:" : "" ) . $k;
                json_expand_util( $v, $ref, ( $tag ? "$tag:" : "" ) . $k, $depth + 1 );
                print "d $depth: depth " . ( 1 + $depth ) . " HASH return from expand ARRAY on $ntag\n" if ref( $v ) eq 'ARRAY' && $debug_rplc;
                print "d $depth: depth " . ( 1 + $depth ) . " HASH return from expand HASH on $ntag\n" if ref( $v ) eq 'HASH' && $debug_rplc;
                print "my added " . ( join " ", @added ) . "\n" if $debug_rplc;
#                print "try clear closed hash, tag $ntag ref = " . ref( $v ) . "\n" if $debug_rplc;
#                if ( ref( $v ) eq 'ARRAY' )
#                {
## increment, clear out matching keys                    
#                    print "clear closed hash\n" if $debug_rplc;
#                    $$ref{ "pos" }++ if !$$ref{ "skipinc" };
#                    $$ref{ "skipinc" } = 1;
#                    %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} =
#                        %{$__json_scratch{ $ref }[ $$ref{ "pos" } - 1 ]};
#                    for ( grep /^$ntag/, keys %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} )
#                    {
#                        print "deleting hash key $_\n" if $debug_rplc;
#                        delete ${$__json_scratch{ $ref }[ $$ref{ "pos" } ]}{ $_ };
#                    }
#                }                
            }
        }
        while ( my ($k, $v ) = each %$json )
        {
            if ( ref( $v ) eq 'ARRAY' )
            {
                my $ntag = ( $tag ? "$tag:" : "" ) . $k;
                if ( ref( $v ) eq 'ARRAY' )
                {
                    if ( $array_count )
                    {
                        die "Error: only one ARRAY currently supported per hash found at $ntag in ?.json\n";
                    }
                    $array_count++;
                }
                json_expand_util( $v, $ref, ( $tag ? "$tag:" : "" ) . $k, $depth + 1 );
                print "d $depth: depth " . ( 1 + $depth ) . " HASH return from expand ARRAY on $ntag\n" if ref( $v ) eq 'ARRAY' && $debug_rplc;
                print "d $depth: depth " . ( 1 + $depth ) . " HASH return from expand HASH on $ntag\n" if ref( $v ) eq 'HASH' && $debug_rplc;
                print "my added " . ( join " ", @added ) . "\n" if $debug_rplc;
                print "try clear closed array, tag $ntag ref = " . ref( $v ) . "\n" if $debug_rplc;
                if ( ref( $v ) eq 'ARRAY' )
                {
# increment, clear out matching keys                    
                    print "clear closed array ntag $ntag\n" if $debug_rplc;
                    $$ref{ "pos" }++ if !$$ref{ "skipinc" };
                    $$ref{ "skipinc" } = 1;
                    %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} =
                        %{$__json_scratch{ $ref }[ $$ref{ "pos" } - 1 ]};
                    my $utag = $ntag;
                    print "clear closed array, utag1 $utag\n" if $debug_rplc;
                    $utag =~ s/\:\w*$//;
                    print "clear closed array, utag $utag\n" if $debug_rplc;
                    for ( grep /^$utag(:|$)/, keys %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} )
                    {
                        print "deleting hash key $_\n" if $debug_rplc;
                        delete ${$__json_scratch{ $ref }[ $$ref{ "pos" } ]}{ $_ };
                    }
                }                
            }
        }
    }
    if ( ref( $json ) eq 'ARRAY' )
    {
        my @added;
        for ( my $i = 0; $i < @$json; ++$i )
        {
            my $v = $$json[ $i ];
            if ( ref( $v ) )
            {
                json_expand_util( $v, $ref, $tag, $depth + 1 );
                print "d $depth: depth " . ( 1 + $depth ) . " ARRAY return from expand ARRAY on $tag\n" if ref( $v ) eq 'ARRAY' && $debug_rplc;
                print "d $depth: depth " . ( 1 + $depth ) . " ARRAY return from expand HASH on $tag\n" if ref( $v ) eq 'HASH' && $debug_rplc;
                print "my added " . ( join " ", @added ) . "\n" if $debug_rplc;
                if ( ref( $v ) eq 'HASH' )
                {
# increment, clear out matching keys                    
                    print "clear closed hash\n" if $debug_rplc;
                    $$ref{ "pos" }++ if !$$ref{ "skipinc" };
                    $$ref{ "skipinc" } = 1;
                    %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} =
                        %{$__json_scratch{ $ref }[ $$ref{ "pos" } - 1 ]};
                    for ( grep /^$tag(:|$)/, keys %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} )
                    {
                        print "deleting hash key $_\n" if $debug_rplc;
                        delete ${$__json_scratch{ $ref }[ $$ref{ "pos" } ]}{ $_ };
                    }
                    print "after delete pairs:\n" if $debug_rplc;
                    while ( my ( $k1, $v1 ) = each %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} )
                    {
                        print "$k1 $v1\n" if $debug_rplc;
                    }
                }                
            } else {
                print '-'x60 . "\n" if $debug_rplc;
                show_state( $ref, "pre adding array element '$tag' '$v'", $depth ) if $debug_rplc;
                if ( $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $tag } )
                {
                    $$ref{ "pos" }++;
                    %{$__json_scratch{ $ref }[ $$ref{ "pos" } ]} =
                        %{$__json_scratch{ $ref }[ $$ref{ "pos" } - 1 ]};
                    $$ref{ "skipinc" } = 0;
                }
                $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $tag } = $v;
                push @added, $tag;
                show_state( $ref, "post adding array element '$tag' '$v'", $depth ) if $debug_rplc;
            }
        }
        if ( 0 ) {
            for ( my $i = 0; $i < @$json; ++$i )
            {
                my $v = $$json[ $i ];
                if ( ref( $v ) )
                {
                    json_expand_util( $v, $ref, $tag, $depth + 1 );
                }
            }
        }
    }
}    

sub start_json {
    my $json = $_[0];
    my $ref  = $_[1];    # a reference to a HASH 
    
    my $error;
    if ( ref( $json ) ne 'HASH' )
    {
        $error .= "start_json argument 1 is not a HASH\n";
    }
    if ( ref( $ref ) ne 'HASH' )
    {
        $error .= "start_json argument 2 is not a HASH\n";
    }
    die $error if $error;

    $__json_scratch{ $ref } = (); # an array of pairs
    $$ref{ "pos" }     = 0;
    $$ref{ "skipinc" } = 0;

    json_expand_util( $json, $ref );
    if ( $$ref{ "pos" } ) # -1 to take care of last closer ?
    {
        $__json_scratch{ $ref }[ $$ref{ "pos" } ] = 0;
        $$ref{ "pos" }--;
#        pop( $__json_scratch{ $ref } );
    }
    $$ref{ "mpos" } = $$ref{ "pos" };
    $$ref{ "pos" } = 0;
#    print "ref( $json ) is " . ref( $json ) . "\n";
#    print "ref( $ref ) is " . ref( $ref ) . "\n";
    $__json_scratch{ $ref }[ $$ref{ "pos" } ];
}

sub rewind_json {
    $$ref{ "pos" } = 0;
    return $__json_scratch{ $ref }[ $$ref{ "pos" } ];
}

sub copy_json {
    my $org_ref = $_[ 0 ];
    my $new_ref = $_[ 1 ];
    $__json_scratch{ $new_ref } = [ @$__json_scratch{ $org_ref } ];
}

sub next_json {
    my $ref   = $_[ 0 ];
    my $match = $_[ 1 ];
    my @match = split '\|', $match;

    if ( !$match )
    {
        $$ref{ "pos" }++;
    } else {
        my @v;
        for ( my $i = 0; $i < @match; ++$i )
        {
            $v[ $i ] = $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $match[ $i ] };
        }
#        print "start next json: match: " . ( join '~', @match ) . "\n";
#        print "start next json: v    : " . ( join '~', @v     ) . "\n";
        my $goon = 1;
        do {
            $$ref{ "pos" }++;
            for ( my $i = 0; $i < @match; ++$i )
            {
                if (  $__json_scratch{ $ref }[ $$ref{ "pos" } ]{ $match[ $i ] } ne $v[ $i ] )
                {
                    $goon = 0;
                    last;
                }
            }
        } while ( $$ref{ "pos" } < $$ref{ "mpos" } && $goon );
        if ( $goon )
        {
            return 0;
        }
#        print "end of match check: pos " . $$ref{ "pos" } . " mpos: " . $$ref{ "mpos" } . "\n";
    }
    return $__json_scratch{ $ref }[ $$ref{ "pos" } ];
}    


sub get_replacements {
    my $t = $_[ 0 ];
    if ( ref( $t ) ne 'ARRAY' )
    {
        my @r = split "\n", $t;
        return get_replacements( \@r );
    }
    my %used;
    my @ret;
    foreach my $k ( @$t )
    {
        while ( ( $tok ) = $k =~ /__([a-z0-9:]+)__/ )
        {
            push @ret, $tok if !$used{ $tok }++;
            $k =~ s/__${tok}__//g;
            print "k after removal of __${tok}__ is $k\n" if $debug_rplc;
        }
    }
    \@ret;
}

sub test_get_replacements {
    my @l = (
        "__line1__",
        "__line2__line3a__",
        "__line2____line3b__",
        "__tagx__ __tagy__"
        );
    my $r = get_replacements( \@l );
    print "test_get_replacements:\n";
    for ( my $i = 0; $i < @$r; ++$i )
    {
        print "$$r[ $i ]\n";
    }
}

sub fix_up_sub_tok {
    my $tok = $_[ 0 ];
    $tok =~ s/\\/\\\\/g;
    $tok =~ s/\+/\\+/g;
    $tok =~ s/\(/\\(/g;
    $tok =~ s/\)/\\)/g;
    $tok =~ s/\*/\\*/g;
    $tok =~ s/\{/\\{/g;
    $tok =~ s/\}/\\}/g;
    $tok =~ s/\^/\\^/g;
    $tok =~ s/\$/\\\$/g;
    $tok =~ s/\[/\\[/g;
    $tok =~ s/\]/\\]/g;
    $tok;
}

sub get_cond_replacements {
#    $debug_rplc++;
    my $t = $_[ 0 ];
    if ( ref( $t ) ne 'ARRAY' )
    {
        my @r = split "\n", $t;
        return get_cond_replacements( \@r );
    }
    my %used;
    my %ret;
    my @l = @$t; # make copy to avoid side effect of destroying fields
    foreach my $k ( @l )
    {
        print "testing: <$k>\n" if $debug_rplc;
#        while ( ( $tok1, $tok2 ) = $k =~ /__\~([a-z0-9:]+)\s*\{([a-zA-Z0-9_=\"\': \+\-\(\)\*;%\.>]+)/ )
        while ( ( $tok1, $tok2 ) = $k =~ /__\~([a-z0-9:_]+)\s*\{(([a-zA-Z0-9_=\"\': \+\-\(\)\*;#%\.\,\^>\/<~\\\$\[\]]|\{[^\}]*\})+)\}/ )
        {
            print "--- tok1 $tok1 : $tok2\n" if $debug_rplc;
            if ( $used{ $tok1 }++ )
            {
                die "Error: multiple condition replacements for $tok1\n";
            }
            $ret{ $tok1 } = $tok2;
            my $tok2r = fix_up_sub_tok( $tok2 );
            $k =~ s/__~${tok1}\s*\{$tok2r\}//g;
            print "k after removal of __~${tok1}\s*\{$tok2\} is $k\n" if $debug_rplc;
        }
        print "testing <$k> done\n" if $debug_rplc;
    }
    undef $debug_rplc;
    \%ret;
}

sub test_get_cond_replacements {
    my @l = (
        '__~myrplcxy',
        '__~myrplcxy2{ max="__hi__"}',
        '__~myrplc1{ max="__max__"}',
        '__~myrplc2{ max="__max__"}__~myrplc3{ max="__max__"}'
        );
    print "test_get_cond_replacements:\n";
    my $r = get_cond_replacements( \@l );
    while( my ( $k, $v ) = each %$r )
    {
        print "$k => $v\n";
    }
}

sub mkdir_for_file {
    my $f = $_[ 0 ];
    my @l = split '/', $f;
    my $p;
    for ( my $i = 0; $i < @l -1; ++$i )
    {
        $p .= "/" if $p;
        $p .= $l[ $i ];
        if ( !-d $p )
        {
            if ( -e $p )
            {
                die "Error: creating directory $p, it is not a directory and already exists\n";
            }
            if ( ! mkdir $p )
            {
                die "Error: creating directory $p $!\n";
            }
            $notice .= "created directory $p\n";
        }
    }
}

sub print_json_expand {
    my $arg = $_[ 0 ];
    my $pos = $_[ 1 ];
    if ( ref( $arg ) eq 'HASH' )
    {
        while ( my ($k, $v ) = each %$arg )
        {
            if ( ref( $v ) )
            {
                print '-'x$pos . "$k is a ref:\n";
                print_json_expand( $v, $pos + 1 );
            } else {
                print '-'x$pos . "$k is $v\n";
            }
        }
    }
    if ( ref( $arg ) eq 'ARRAY' )
    {
        for ( my $i = 0; $i < @$arg; ++$i )
        {
            my $v = $$arg[ $i ];
            if ( ref( $v ) )
            {
                print '-'x$pos . "[$i] is a ref:\n";
                print_json_expand( $v, $pos + 1 );
            } else {
                print '-'x$pos . "[$i] is $v\n";
            }
        }
    }
}    

sub get_file_json {
    my $f = $_[ 0 ];

    die "$0: get_file_json error $f does not exist\n" if !-e $f;
    my $fh;
    open $fh, $f || die "$0: get_file_json error file open $f $!\n";
    my @l = <$fh>;
    close $fh;
    @l = grep !/^\s*#/ , @l;
    my $l = join '', @l;
    decode_json( $l );
}

sub get_lang_json {
    get_file_json( "$gap/languages/$_[0].json" );
}

# $debugref++;

sub ref_match {
    my $arg   = $_[ 0 ];
    my $match = $_[ 1 ];
    my $tag   = $_[ 2 ];
    my $pos   = $_[ 3 ];

    my @ret;
    
    $tag = "base" if !length( $tag );

    if ( ref( $arg ) eq 'HASH' )
    {
        while ( my ($k, $v ) = each %$arg )
        {
            if ( $k eq $match )
            {
                push @ret, $v;
            }
            if ( ref( $v ) )
            {
                print '-'x$pos . "$k is a ref:\n" if $debugref;
                my $x = ref_match( $v, $match, $k, $pos + 1 );
                foreach my $k ( @$x )
                {
                    push @ret, $k;
                }
            } else {
                print '-'x$pos . "<[$tag]> <$k> is $v\n" if $debugref;
            }
        }
    }
    if ( ref( $arg ) eq 'ARRAY' )
    {
        for ( my $i = 0; $i < @$arg; ++$i )
        {
            my $v = $$arg[ $i ];
            if ( ref( $v ) )
            {
                print '-'x$pos . "[$i] is a ref:\n" if $debugref;
                my $x = ref_match( $v, $match, $tag, $pos + 1 );
                foreach my $k ( @$x )
                {
                    push @ret, $k;
                }
            } else {
                print '-'x$pos . "<[$tag]> [$i] is $v\n" if $debugref;
            }
        }
    }

    \@ret;
}    

sub hash_simple {
# for single pair info
    my $x = ref_match( $_[ 0 ], $_[ 1 ] );
    my %ret;
    foreach my $k ( @$x )
    {
        $ret{ $k }++ if !ref( $k );
        if ( ref( $k ) eq 'ARRAY' )
        {
            foreach my $j ( @$k )
            {
                $ret{ $j }++ if !ref( $j );
            }
        }
    }
    \%ret;
}

sub hash_sub {
    my $modules = ref_match( $_[ 0 ], $_[ 1 ], $_[ 2 ] );
    my %ret;

    foreach my $k ( @$modules )
    {
        if ( ref( $k ) eq 'ARRAY' )
        {
            foreach my $j ( @$k )
            {
                if ( ref( $j ) eq 'HASH' )
                {
                    while( my ( $k, $v ) = each %$j )
                    {
                        $ret{ $v }++ if !ref( $v ) && $k eq $_[ 2 ];
                    }
                }
            }
        }
    }
    \%ret;
}
    
sub valid_name {
    my $context = $_[ 0 ];
    my $ref     = $_[ 1 ];
    my $error;
    my %tmpref;

    if ( ref( $ref ) eq 'SCALAR' )
    {
        $ref = { $$ref => 1 };
    }

    if ( ref( $ref ) eq 'ARRAY' )
    {
        my $bref = $ref;
        $ref = {};
        foreach my $k ( @$bref )
        {
            $$ref{ $k }++;
        }
    }
    foreach my $k ( keys %$ref )
    {
        if ( $k !~ /^[A-Za-z]\w*$/ )
        {
            $error .= "in context $context: invalid name '$k' must alphabetic in first space and contain only alphanumeric or underscore\n";
        }
        if ( $reserved_words{ $k } )
        {
            $error .= "in context $context: invalid name '$k' reserved word\n";
        }
    }
    $error;
}

sub check_files {
    print "genapp_util.pl version $gap_version\n";

    # global side-effects
    undef %langs;
    undef %modules;
    undef %icons;
    undef %types;
    undef $menu;
    undef $config;
    undef $directives;
    undef %rpls;
    undef %module_to_file;
    undef %reserved_words;

    $notice .= load_reserved_words();


    my @req = (
        'directives.json', 
        'menu.json',
        'config.json'
    );

    my $error;
    my $warn;

#    $error .= valid_name( 'testing', [ "hi", "there", "interface" ] );

    my @further_checks;
    my $fh;
    foreach my $f ( @req )
    {
        if ( !-e $f )
        {
            if ( -e "$gap/modules/$f" )
            {
                $f = "$gap/modules/$f";
            } else {
                $error .= "$f does not exist\n";
                next;
            }
        }
        if ( ! open $fh, $f )
        {
            $error .= "$f can not be opened\n";
            next;
        }
        close $fh;
        push @further_checks, $f;
    }

    foreach my $f ( @further_checks )
    {
        print '-'x60 . "\n";
        print "checking: $f\n";
        my $json = get_file_json( $f );
        print "get json $f: json $json\n";
        {
            my $x = hash_simple( $json, 'languages' );
            print "languages:\n\t" . ( join "\n\t", keys %$x ) . "\n" if keys %$x;
            foreach my $k ( keys %$x )
            {
                $langs{ $k }++;
            }
        }

        {
            my $x = hash_sub( $json, 'modules', 'id' );
            print "modules:\n\t" . ( join "\n\t", keys %$x ) . "\n" if keys %$x;
            $error .= valid_name( "$f modules:id", $x );
            foreach my $k ( keys %$x )
            {
                $modules{ $k }++;
            }
            
        }

        {
            my $x = hash_simple( $json, 'icon' );
            print "icons:\n\t" . ( join "\n\t", keys %$x ) . "\n" if keys %$x;
            foreach my $k ( keys %$x )
            {
                $icons{ $k }++;
            }
        }

        # special file specific items
        if ( $f eq 'directives.json' )
        {
            $directives = $json;
            my @req = ( "title", "application", "version" );
            foreach my $k ( @req )
            {
                my $x = hash_simple( $json, $k );
                if ( keys %$x == 0 )
                {
                    $error .= "no $k found in $f\n";
                    next;
                }
                if ( keys %$x != 1 )
                {
                    $warn .= "multiple $k found: " . ( join ' ', keys %$x ) . " in $f\n";
                }
                $rpls{ $k } = each %$x;
                print "$k:\t". $rpls{$k} . "\n";
            }
            my @cnames = ( "application" );
            foreach my $k ( @cnames )
            {
                $error .= valid_name( "$f \"$k\"", \$rpls{$k} );
            }
        }

        if ( $f eq 'menu.json' )
        {
            $menu = $json;
        }

        if ( $f eq 'config.json' ||
             $f eq "$gap/modules/config.json" )
        {
            $config = $json;
        }
    }

    foreach my $k ( keys %langs )
    {
        my $f = "$gap/languages/$k.json";
        if ( !-e $f )
        {
            $error .= "missing language '$k' in $f \n";
            next;
        }
    }

    my %module_files;

    foreach my $k ( keys %modules )
    {
# local dir modules take precedence
        my $f = "modules/$k.json";
        if ( !-e $f )
        {
#            my $fg = "$gap/modules/" . $rpls{"application"} . "/$k.json";
            my $fg = "$gap/modules/$k.json";
            if ( !-e $fg )
            {
                $error .= "missing module '$k' in $f or $fg\n";
                next;
            } else {
                $module_files{ $fg }++;
                $module_to_file{ $k } = $fg;
            }
        } else {
            $module_files{ $f }++;
            $module_to_file{ $k } = $f;
        }
    }

    foreach my $k ( keys %icons )
    {
        my $f = $k;
        if ( !-e $f )
        {
            $error .= "missing icon '$k' in $f \n" if !-e $f;
            next;
        }
    }


# check module types for validity

    my %types;

# we should redo this to check for "role"

    foreach my $f ( keys %module_files )
    {
        print "checking module file $f\n";
        my $json = get_file_json( $f );
        
        # check types for valid registry
        {
            my $x = hash_simple( $json, 'type' );
            print "types:\n\t" . ( join "\n\t", keys %$x ) . "\n" if keys %$x && $debug;
            foreach my $k ( keys %$x )
            {
                $types{ $k }++;
            }
        }
        # check reserved words
        {
            my $x = hash_simple( $json, 'id' );
            print "id:\n\t" . ( join "\n\t", keys %$x ) . "\n" if keys %$x && $debug;
            $error .= valid_name( "$f \"id\"", $x );
        }
    }

    foreach my $l ( keys %langs )
    {
        print "checking language types for language $l\n";
        foreach my $k ( keys %types )
        {
            print "checking language types for language $l type $k\n";
            my $b = "$gap/languages/$l/types/$k";
            {
                my $f = "$b.input";
                if ( !-e $f )
                {
                    $error .= "missing required file $f\n";
                }
            }
            {
                my $f = "$b.output";
                if ( !-e $f )
                {
                    $error .= "missing required file $f\n";
                }
            }
        }
    }
    

# ------------------------------------------------------------------



    my $retval = 1;
    if ( $warn )
    {
        print '-'x60 . "\nWarnings:\n$warn" . '-'x60 . "\n";
    }
    if ( $error )
    {
        print '-'x60 . "\nErrors:\n$error" . '-'x60 . "\n";
        $retval = 0;
    }
    return $retval;
}

return true;
