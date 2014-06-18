#!/usr/bin/perl

$notes = "usage: $0 {-d{?}}
checks the files
note: env variable GENAPP must be defined
options:
-d debug mode
 -dr show replacements
 -dc conditional replacements
 -ds show inline replacements
 -do special for once:field:id messages
-h prints this message
" ;

$gap = $ENV{ "GENAPP" } || die "$0: error env variable GENAPP must be defined\n";

while ( $ARGV[ 0 ] =~ /^-(\w{1,2})/ )
{
    my $arg = $1;
    print "arg is $arg\n";
    shift;
    if ( $arg =~ /^d(\w?)/ )
    {
        if ( $1 eq 'r' )
        {
            $debug_rplc++;
        }
        if ( $1 eq 'c' )
        {
            $debug_crplc++;
        }
        if ( $1 eq 's' )
        {
            $debug_srplc++;
        }
        if ( $1 eq 'o' )
        {
            $debug_oncefield++;
            next;
        }
        $debug_main++;
        next;
    }
    if ( $arg eq 'h' )
    {
        print $notes;
        exit;
    }
    die "$0: unrecognized option '-$1'\n\n$notes";
}

require "$gap/etc/perl/genapp_util.pl";

check_files() || die "Errors found\n";

print "preliminary checks ok\n";

# test_get_replacements();
# test_get_cond_replacements();

my $error;
my $warn;
my $notice;
my $created;

my $ref_directives = {};
my $ref_menu       = {};
#my $ref_config     = {};
my $rplc           = {};

# $rplc{ "directives" } = start_json( $directives, $ref_directives );
$rplc_directives = start_json( $directives, $ref_directives );

while ( my ( $k, $v ) = each $rplc_directives )
{
    print "$k $v\n" if $debug_srplc;
}
print "menu start_json\n" if $debug_main;
# $rplc{ "menu" }       = start_json( $menu      , $ref_menu       );

foreach my $l ( keys %langs )
{
    print '-'x60 . "\n";
    print "processing language $l\n";
    print '-'x60 . "\n";
    $rplc_menu   = start_json( $menu,   $ref_menu );
#    $rplc_config = start_json( $config, $ref_config );
#    $rplc{ "directives" } = rewind_json( $ref_directives );
    $lang = get_lang_json( $l );
    undef %created;
    for ( my $i = 0; $i < @{@$lang{ "assembly" }}; ++$i ) # for each assembly step (from language)
    {
        print '='x80 . "\n";
        my $use    = $$lang{ "assembly" }[ $i ];
        my $freq   = $$use{ "frequency" };
        my $output = $$use{ "output" };
        my $inputs = $$use{ "inputs" };
        if ( !$freq )
        {
            $notice .= "language $l: no frequency defined for assembly step " . ( $i + 1 ) . " defaulting to 'once'\n";
            $freq = "once";
        }
        if ( !$output )
        {
            $warn .= "language $l: no output defined for assembly step " . ( $i + 1 ) . "\n";
            next;
        }

        print "assembly freq:   $freq\n";
        print "assembly output: $output\n";

        if ( !@$inputs )
        {
            $warn .= "language $l: no inputs defined for assembly step " . ( $i + 1 ) . " with output $output\n";
            $freq = "once";
        }

        my $mod_json;
        my $rplc_mod;
        my $ref_mod = {};

        print "freq $freq\n";
        if ( $freq eq 'menu:modules:id' )
        {
            $rplc_menu = start_json( $menu, $ref_menu );
            my $mod  = $$rplc_menu{ $freq };
            my $mod_f = $module_to_file{ $mod };
# this should probably be loaded once in check_files()
            $mod_json = get_file_json( $mod_f );
            $rplc_mod = start_json( $mod_json, $ref_mod );
##           $rplc_menu = rewind_json( $ref_menu );
#            do {
#                print "menu:modules:id " . $$rplc_menu{ $freq } . "\n";
#            } while( $rplc_menu = next_json( $ref_menu, $freq ) );
        }


        if ( $freq eq 'config:modules:id' )
        {
            $rplc_menu = start_json( $config, $ref_menu );
            my $mod  = $$rplc_menu{ 'menu:modules:id' };
            print "mod = $mod\n";
            my $mod_f = $module_to_file{ $mod };
            print "mod_f = $mod_f\n";
# this should probably be loaded once in check_files()
            $mod_json = get_file_json( $mod_f );
            $rplc_mod = start_json( $mod_json, $ref_mod );
        }

        if ( $freq eq 'menu:id' )
        {
            $rplc_menu = start_json( $menu, $ref_menu );
            my $mod  = $$rplc_menu{ 'menu:modules:id' };
            my $mod_f = $module_to_file{ $mod };
# this should probably be loaded once in check_files()
            $mod_json = get_file_json( $mod_f );
            $rplc_mod = start_json( $mod_json, $ref_mod );
##           $rplc_menu = rewind_json( $ref_menu );
#            do {
#                print "menu:modules:id " . $$rplc_menu{ $freq } . "\n";
#            } while( $rplc_menu = next_json( $ref_menu, $freq ) );
        }

        do {
            my $outdata;
            print "->-> module is now " . $$rplc_menu{ "menu:modules:id" } . "\n" if $debug_main;
            print "->-> menu:id is now " . $$rplc_menu{ "menu:id" } . "\n" if $debug_main;

            for ( my $i = 0; $i < @$inputs; ++$i )
            {
                while( my ( $k, $v ) = each %{$$inputs[ $i ]} )
                {
                    print '~'x60 . "\n" if $debug_main;
                    print "processing: $k $v\n" if $debug_main;
                    my $use_input = $k;
                    if ( $freq =~ /^(menu|config):modules:id$/ )
                    {
# this should probably be loaded once in check_files()
                        $mod_json = get_file_json( $module_to_file{ $$rplc_menu{ 'menu:modules:id' } } );
                        $rplc_mod = start_json( $mod_json, $ref_mod );
#                       $rplc_mod = rewind_json( $ref_mod );
                        print "--------- input module rplc ---------\n" if $debug_srplc;
                        while ( my ( $k, $v ) = each $rplc_mod )
                        {
                            print "s/__${k}__/${v}/g\n" if $debug_srplc;
                            $use_input =~ s/__${k}__/${v}/g;
                        }
                        print "--------- end input module rplc ---------\n" if $debug_srplc;
                        print "use input is $use_input\n" if $debug_main;
                    }
# special case:
                    if ( $freq eq 'once' && $v eq 'fields:id' )
                    {
# need to loop through all modules, all fields
                        my $use_freq = 'menu:modules:id';
                        my $rplc_menu = start_json( $menu, $ref_menu );
                        do {
                            my $mod  =  $$rplc_menu{ $use_freq };
                            my $mod_f = $module_to_file{ $mod };
                            print "module file $mod_f\n" if $debug_main || $debug_oncefield;
                            my $mod_json = get_file_json( $mod_f );
                            my $ref_mod = {};
                            my $rplc_mod = start_json( $mod_json, $ref_mod );
                            do {
                                print " field " . $$rplc_mod{ $v } . " " . $$rplc_mod{ 'fields:type' } . "\n" if $debug_main || $debug_oncefield;
                                my $use_input = $k;
                                while ( my ( $k, $v ) = each $rplc_mod )
                                {
                                    print "s/__${k}__/${v}/g\n" if $debug_main || $debug_oncefield;
                                    $use_input =~ s/__${k}__/${v}/g;
                                }
                                print "use input is now $use_input\n" if $debug_oncefield;
                                my $f = "$gap/languages/$l/$use_input";
                                if ( !-e $f )
                                {
                                    $notice .= "Skipping non-existant input $f\n" if ( $debug_main || $debug_oncefield ) && !$noticed_missing{ $f }++;
# this 'next' should work? 
#                                    next;
                                } else {
                                    my $fh;
                                    if ( ! open $fh, $f )
                                    {
                                        $error .= "language $l: assembly step " . ( $i + 1 ) . ": error opening input file $f $!\n";
                                        next;
                                    }
                                    my @l = <$fh>;
                                    close $fh;
# do replacements
                                    while ( my ( $k, $v ) = each $rplc_directives )
                                    {
                                        print "directives: s/__${k}__/${v}/g\n" if $debug_srplc || $debug_oncefield;
                                        grep s/__${k}__/${v}/g, @l;
                                    }
                                    while ( my ( $k, $v ) = each $rplc_menu )
                                    {
                                        print "menu: s/__${k}__/${v}/g\n" if $debug_srplc || $debug_oncefield;
                                        grep s/__${k}__/${v}/g, @l;
                                    }
                                    while ( my ( $k, $v ) = each $rplc_mod )
                                    {
                                        print "mod: s/__${k}__/${v}/g\n" if $debug_srplc || $debug_oncefield;
                                        grep s/__${k}__/${v}/g, @l;
                                    }
                                    print "get cond rplc1\n" if $debug_crplc;
                                    for ( my $sp = 0; $sp < @l; ++$sp )
                                    {
                                        my @lu;
                                        push @lu, $l[ $sp ];
                                        my $cond_r = get_cond_replacements( \ @lu );
                                        while ( my ( $k, $v ) = each %$cond_r )
                                        {
                                            if ( defined $$rplc_mod{ $k } &&
                                                 lc( $$rplc_mod{ $k } ) ne 'false' )
                                            {
                                                print "1: $k => $v\n" if $debug_crplc;
                                                my $vr = fix_up_sub_tok( $v );
                                                grep s/__~${k}\s*\{$vr\}/$v/, @lu;
                                            } else {
                                                print "1: $k => $v blanked\n" if $debug_crplc;
                                                my $vr = fix_up_sub_tok( $v );
                                                grep s/__~${k}\s*\{$vr\}//, @lu;
                                            }
                                        }
                                        print "adding to outdata------------------------------\n" if $debug_srplc || $debug_oncefield;
                                        $outdata .= join '', @lu;
                                    }
                                }
                            } while ( $rplc_mod = next_json( $ref_mod, $v ) );
                        } while( $rplc_menu = next_json( $ref_menu, $use_freq ) );
                        next;
                    }

                    my $f = "$gap/languages/$l/$use_input";
                    if ( !-e $f )
                    {
                        $error .= "language $l: assembly step " . ( $i + 1 ) . ": missing input file $f\n";
                        next;
                    }
                    my $fh;
                    if ( ! open $fh, $f )
                    {
                        $error .= "language $l: assembly step " . ( $i + 1 ) . ": error opening input file $f $!\n";
                        next;
                    }
                    my @l = <$fh>;
                    close $fh;
                    my @l_sav = @l;
# do replacements
                    my $r = get_replacements( \ (@l) );
                    for ( my $i = 0; $i < @$r; ++$i )
                    {
                        print "$f replacement $$r[ $i ]\n" if $debug_srplc;
                    }
                    while ( my ( $k, $v ) = each $rplc_directives )
                    {
                        print "directives: s/__${k}__/${v}/g\n" if $debug_srplc;
                        grep s/__${k}__/${v}/g, @l;
                    }

                    if ( $$rplc_menu{ "menu:modules:id" } )
                    {
                        print "mod_f is " . $$rplc_menu{ "menu:modules:id" } . "\n" if $debug_srplc;
                        my $mod_json = get_file_json( $module_to_file{ $$rplc_menu{ "menu:modules:id" } } );
                        my $ref_mod = {};
                        my $rplc_mod = start_json( $mod_json, $ref_mod );
#                        while ( my ( $k, $v ) = each $rplc_menu )
#                        {
#                            print "nd:menu: s/__${k}__/${v}/g\n" if $debug_srplc || $debug_oncefield;
#                        }
#                        while ( my ( $k, $v ) = each $rplc_mod )
#                        {
#                            print "nd:mod: s/__${k}__/${v}/g\n" if $debug_srplc || $debug_oncefield;
#                        }
                        {
                            print "get cond rplc2\n" if $debug_crplc;
                            for ( my $sp = 0; $sp < @l; ++$sp )
                            {
                                my @lu;
                                push @lu, $l[ $sp ];
                                my $cond_r = get_cond_replacements( \ @lu );
                                while ( my ( $k, $v ) = each %$cond_r )
                                {
                                    if ( ( defined $$rplc_directives{ $k } &&
                                           lc( $$rplc_directives{ $k } ) ne 'false' ) ||
                                         ( defined $$rplc_mod{ $k } &&
                                           lc( $$rplc_mod{ $k } ) ne 'false' ) )
                                    {
                                        print "1: $k => $v\n" if $debug_crplc;
                                        my $vr = fix_up_sub_tok( $v );
                                        grep s/__~${k}\s*\{$vr\}/$v/, @lu;
                                    } else {
                                        print "1: $k => $v blanked\n" if $debug_crplc;
                                        my $vr = fix_up_sub_tok( $v );
                                        grep s/__~${k}\s*\{$vr\}//, @lu;
                                    }
                                }
                                $l[ $sp ] = $lu[ 0 ];
                            }
                        }
                    } else {
                        print "get cond rplc2b\n" if $debug_crplc;
                        for ( my $sp = 0; $sp < @l; ++$sp )
                        {
                            my @lu;
                            push @lu, $l[ $sp ];
                            my $cond_r = get_cond_replacements( \ @lu );
                            while ( my ( $k, $v ) = each %$cond_r )
                            {
                                if ( ( defined $$rplc_directives{ $k } &&
                                       lc( $$rplc_directives{ $k } ) ne 'false' ) )
                                {
                                    print "1: $k => $v\n" if $debug_crplc;
                                    my $vr = fix_up_sub_tok( $v );
                                    grep s/__~${k}\s*\{$vr\}/$v/, @lu;
                                } else {
                                    print "1: $k => $v blanked\n" if $debug_crplc;
                                    my $vr = fix_up_sub_tok( $v );
                                    grep s/__~${k}\s*\{$vr\}//, @lu;
                                }
                            }
                            $l[ $sp ] = $lu[ 0 ];
                        }
                    }
# is freq menu:id
                    if ( $v eq 'menu:id' )
                    {
                        if ( $freq ne 'once' )
                        {
                            $error .= "in $k $v: menu inputs frequency of 'menu:id' requires an assembly frequency of 'once'\n";
                            next;
                        }
                        print "menu loop\n" if $debug_main;
                        my $ref_menu2 = {};
                        my $rplc_menu2 = start_json( $menu, $ref_menu2 );
#                       $rplc_menu2 = rewind_json( $ref_menu2 );
                        do {
                            @l = @l_sav;
                            while ( my ( $k, $v ) = each $rplc_directives )
                            {
                                print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                grep s/__${k}__/${v}/g, @l;
                            }
                            while ( my ( $k, $v ) = each $rplc_menu2 )
                            {
                                print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                grep s/__${k}__/${v}/g, @l;
                            }
                            print "adding to outdata------------------------------\n" if $debug_srplc;
                            $outdata .= join '', @l;
                        } while( $rplc_menu2 = next_json( $ref_menu2, $v ) );
                    } else {
                        if ( $v =~ /^(menu|config):modules:id$/ )
                        {
                            print "hello menu:modules:id\n" if $debug_main;
                            my $ref_menu2 = {};
                            my $rplc_menu2 = start_json( $menu, $ref_menu2 );
                            while ( $rplc_menu2 && $$rplc_menu2{ 'menu:id' } ne $$rplc_menu{ 'menu:id' } )
                            {
                                $rplc_menu2 = next_json( $ref_menu2, 'menu:id' );
                            }
                            if ( $$rplc_menu2{ 'menu:id' } ne $$rplc_menu{ 'menu:id' } )
                            {
                                $error .= "menu lookup error in $l $v\n";
                            }
                            do {
                                print "rplc menu:id " . $$rplc_menu2{ "menu:id" } . " " . $$rplc_menu{ 'menu:id' } . "\n" if $debug_srplc; 
                                @l = @l_sav;
                                while ( my ( $k, $v ) = each $rplc_directives )
                                {
                                    print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                    grep s/__${k}__/${v}/g, @l;
                                }
                                while ( my ( $k, $v ) = each $rplc_menu2 )
                                {
                                    print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                    grep s/__${k}__/${v}/g, @l;
                                }
                                print "adding to outdata------------------------------\n" if $debug_srplc;
                                $outdata .= join '', @l;
                                $rplc_menu2 = next_json( $ref_menu2, $v );
                            } while ( $rplc_menu2 && 
                                      ($freq ne 'menu:id' || $$rplc_menu2{ 'menu:id' } eq $$rplc_menu{ 'menu:id' } )
                                      );
                        } else {
# todo if $freq = 'module' find specific module json and make subs (add support in genapp_util ?
                            if ( ref( $rplc_menu ) eq 'HASH' )
                            {
                                while ( my ( $k, $v ) = each $rplc_menu )
                                {
                                    print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                    grep s/__${k}__/${v}/g, @l;
                                }
                            }
                            if ( $freq =~ /^(menu|config):modules:id$/ )
                            {
                                $rplc_mod = start_json( $mod_json, $ref_mod );
#                               $rplc_mod = rewind_json( $ref_mod );
                                print "--------- menu/module rplc ---------\n" if $debug_srplc;
                                while ( my ( $k, $v ) = each $rplc_mod )
                                {
                                    print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                    grep s/__${k}__/${v}/g, @l;
                                }
                                print "--------- end menu/module rplc ---------\n" if $debug_srplc;
                                my $ex = $$rplc_mod{ 'executable' };
                                print "exec $ex\n" if $debug_smain;
                            }
                            if ( $v eq 'fields:id' )
                            {
                                my $role = $k;
                                $role =~ s/^.*\.//;
                                print "fields:id module loop ___________ k is $k, role $role\n" if $debug_srplc;
                                
                                $rplc_mod = start_json( $mod_json, $ref_mod );
#                               $rplc_mod = rewind_json( $ref_mod );
                                do {
                                    if ( !defined $$rplc_mod{ 'fields:role' } )
                                    {
                                        $warn .= "fields:role not defined in module " . $$rplc_mod{ 'moduleid' } . " " . "fields:id " . $$rplc_mod{ 'fields:id' } . "\n";
                                    }
                                    print "field " .  $$rplc_mod{ 'fields:id' } . " role " . $$rplc_mod{ 'fields:role' } . "\n" if $debug_srplc;
                                    if ( $$rplc_mod{ 'fields:role' } eq $role )
                                    {
                                        my $use_input = $k;
                                        while ( my ( $k, $v ) = each $rplc_mod )
                                        {
                                            print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                            $use_input =~ s/__${k}__/${v}/g;
                                        }
                                        my $f = "$gap/languages/$l/$use_input";
                                        print "processing role input from $f\n" if $debug_main;
                                        if ( !-e $f )
                                        {
                                            $error .= "language $l: assembly step " . ( $i + 1 ) . ": missing input file $f\n";
                                            next;
                                        }
                                        my $fh;
                                        if ( ! open $fh, $f )
                                        {
                                            $error .= "language $l: assembly step " . ( $i + 1 ) . ": error opening input file $f $!\n";
                                            next;
                                        }
                                        my @l = <$fh>;
                                        close $fh;
                                        while ( my ( $k, $v ) = each $rplc_mod )
                                        {
                                            print "s/__${k}__/${v}/g\n" if $debug_srplc;
                                            grep s/__${k}__/${v}/g, @l;
                                        }
                                        print "get cond rplc3\n" if $debug_crplc;
                                        for ( my $sp = 0; $sp < @l; ++$sp )
                                        {
                                            my @lu;
                                            push @lu, $l[ $sp ];
                                            my $cond_r = get_cond_replacements( \ @lu );
                                            while ( my ( $k, $v ) = each %$cond_r )
                                            {
                                                if ( defined $$rplc_mod{ $k } &&
                                                     lc( $$rplc_mod{ $k } ) ne 'false' )
                                                {
                                                    print "2: $k => $v\n" if $debug_crplc;
                                                    my $vr = fix_up_sub_tok( $v );
                                                    if ( $$rplc_mod{ $k } =~ /~/ )
                                                    {
                                                        my @vals = split '~', $$rplc_mod{ $k };
                                                        my $entries_per = () = $v =~ /~(\d+)/g;
                                                        print "entries per $entries_per\n" if $debug_crplc;
                                                        my $v_new;
                                                        for ( my $i = 0; $i < @vals; $i += $entries_per )
                                                        {
                                                            my $v_this = $v;
                                                            for ( my $j = 0; $j < $entries_per; ++$j )
                                                            {
                                                                $v_this =~ s/~$j/$vals[$i + $j ]/;
                                                            }
                                                            $v_new .= $v_this;
                                                        }
                                                        $v = $v_new;
                                                        print "2b: $k => $v\n" if $debug_crplc;
                                                    }
                                                    grep s/__~${k}\s*\{$vr\}/$v/, @lu;
                                                } else {
                                                    print "2: $k => $v blanked\n" if $debug_crplc;
                                                    my $vr = fix_up_sub_tok( $v );
                                                    grep s/__~${k}\s*\{$vr\}//, @lu;
                                                }
                                            }
                                            print "adding to outdata------------------------------\n" if $debug_srplc;
                                            $outdata .= join '', @lu;
                                        }
                                    }
                                } while( $rplc_mod = next_json( $ref_mod, $v ) );
                            } else {
                                $outdata .= join '', @l;
                            }
                        }
                    }
                }
            }

# write output

            my $use_output = $output;
            while ( my ( $k, $v ) = each $rplc_directives )
            {
                print "s/__${k}__/${v}/g\n" if $debug_srplc;
                $use_output =~ s/__${k}__/${v}/g;
            }
            if ( $freq =~ /^((menu|config):modules:id|menu:id)$/ )
            {
                print '-'x40 . "\n" if $debug_srplc;
                print "menu rplcs\n" if $debug_srplc;
                print '-'x40 . "\n" if $debug_srplc;
                while ( my ( $k, $v ) = each $rplc_menu )
                {
                    print "s/__${k}__/${v}/g\n" if $debug_srplc;
                    $use_output =~ s/__${k}__/${v}/g;
                }

                if ( $freq =~ /^(menu|config):modules:id$/ )
                {
                    while ( my ( $k, $v ) = each $rplc_mod )
                    {
                        print "s/__${k}__/${v}/g\n" if $debug_srplc;
                        $use_output =~ s/__${k}__/${v}/g;
                    }
                }
                print '-'x40 . "\n" if $debug_srplc;
                print "use output is $use_output\n" if $debug_main;
            }
            print '^'x20 . "use output $use_output" . '^'x20 . "\n" if $debug_main;

            my $fo = "output/$l/$use_output";
            $error .= "duplicate output for $fo\n" if $created{ $fo }++;
            mkdir_for_file( $fo );
            my $fh;
            if ( !open $fh, ">$fo" )
            {
                $error .= "language $l: assembly step " . ( $i + 1 ) . ": error opening output file $fo\n";
                next;
            }
            print $fh $outdata;
            close $fh;
            $created .= "$fo\n";
            if ( $freq eq 'config:modules:id' )
            {
                $rplc_menu = next_json( $ref_menu, 'menu:modules:id' );
            } else {
                $rplc_menu = next_json( $ref_menu, $freq ) if $freq =~ /^(menu:modules:id|menu:id)$/;
            }
        } while ( $freq =~ /^((menu|config):modules:id|menu:id)$/ && $rplc_menu );
    } # end for assembly step
    # copy over icons
    foreach my $k ( keys %icons )
    {
        my $fo = "output/$l/$k";
        $warn .= "duplicate output for $fo\n" if $created{ $fo }++;
        mkdir_for_file( $fo );
        my $cmd = "cp $k $fo\n";
        $created .= "$fo\n";
        `$cmd`;
    }
    # copy over add/*
    if ( -d "$gap/languages/$l/add" )
    {
        my @add = `(cd $gap/languages/$l/add; find * -type f)`;
        grep chomp, @add;
        foreach my $k ( @add )
        {
            if ( $k !~ /^\./ && $k !~ /\/\./ )
            {
                my $fo = "output/$l/$k";
                $warn .= "duplicate output for $fo\n" if $created{ $fo }++;
                mkdir_for_file( $fo );
                my $cmd = "cp $gap/languages/$l/add/$k $fo\n";
                $created .= "$fo\n";
                `$cmd`;
            }
        }
    }
} # end for language

print '-'x60 . "\nCreated:\n$created" . '-'x60 . "\n" if $created;
print '-'x60 . "\nNotices:\n$notice" . '-'x60 . "\n"  if $notice;
print '-'x60 . "\nWarnings:\n$warn" . '-'x60 . "\n"   if $warn;
print '-'x60 . "\nErrors:\n$error" . '-'x60 . "\n"    if $error;

