######################################################################
# Automatically generated by qmake (1.07a) Sun Aug 10 06:34:08 2014
######################################################################

TEMPLATE = app
INCLUDEPATH += .
INCLUDEPATH += ../lib/
LIBS += `pkg-config --libs glib-2.0` -L/usr/local/lib -lthrift 
QMAKE_CXXFLAGS += -w -Wno-write-strings -DHAVE_INTTYPES_H -DHAVE_NETINET_IN_H  `pkg-config --cflags glib-2.0`
# Input
HEADERS += analyze.h \
           analyze_analyze_1.h \
           analyze_analyze_2.h \
           build.h \
           build_build_1.h \
           build_build_2.h \
           calculate.h \
           calculate_calculate_1.h \
           calculate_calculate_2.h \
           interact.h \
           interact_interact_1.h \
           interact_interact_2.h \
           nadeemtest.h \
           simulate.h \
           simulate_simulate_1.h \
           simulate_simulate_2.h \
           tools.h \
           tools_align.h \
           tools_center.h \
           tools_data_interpolation.h \
           utility_routines.h
SOURCES += nadeemtest.cpp utility_routines.cpp
