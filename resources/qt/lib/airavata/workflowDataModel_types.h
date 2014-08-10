/**
 * Licensed to the Apache Software Foundation (ASF) under one or more
 * contributor license agreements.  See the NOTICE file distributed with
 * this work for additional information regarding copyright ownership.
 * The ASF licenses this file to You under the Apache License, Version 2.0
 * (the "License"); you may not use this file except in compliance with
 * the License.  You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Autogenerated by Thrift Compiler (0.9.1)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
#ifndef workflowDataModel_TYPES_H
#define workflowDataModel_TYPES_H

#include <thrift/Thrift.h>
#include <thrift/TApplicationException.h>
#include <thrift/protocol/TProtocol.h>
#include <thrift/transport/TTransport.h>

#include <thrift/cxxfunctional.h>




typedef struct _Workflow__isset {
  _Workflow__isset() : graph(false) {}
  bool graph;
} _Workflow__isset;

class Workflow {
 public:

  static const char* ascii_fingerprint; // = "F4A50F0EC638C7F66026F9B6678FD89B";
  static const uint8_t binary_fingerprint[16]; // = {0xF4,0xA5,0x0F,0x0E,0xC6,0x38,0xC7,0xF6,0x60,0x26,0xF9,0xB6,0x67,0x8F,0xD8,0x9B};

  Workflow() : templateId("DO_NOT_SET_AT_CLIENTS"), name(), graph() {
  }

  virtual ~Workflow() throw() {}

  std::string templateId;
  std::string name;
  std::string graph;

  _Workflow__isset __isset;

  void __set_templateId(const std::string& val) {
    templateId = val;
  }

  void __set_name(const std::string& val) {
    name = val;
  }

  void __set_graph(const std::string& val) {
    graph = val;
    __isset.graph = true;
  }

  bool operator == (const Workflow & rhs) const
  {
    if (!(templateId == rhs.templateId))
      return false;
    if (!(name == rhs.name))
      return false;
    if (__isset.graph != rhs.__isset.graph)
      return false;
    else if (__isset.graph && !(graph == rhs.graph))
      return false;
    return true;
  }
  bool operator != (const Workflow &rhs) const {
    return !(*this == rhs);
  }

  bool operator < (const Workflow & ) const;

  uint32_t read(::apache::thrift::protocol::TProtocol* iprot);
  uint32_t write(::apache::thrift::protocol::TProtocol* oprot) const;

};

void swap(Workflow &a, Workflow &b);



#endif
