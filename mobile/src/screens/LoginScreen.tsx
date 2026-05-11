import { useMutation } from "@tanstack/react-query";
import { useState } from "react";
import { Alert, Button, StyleSheet, Text, TextInput, View } from "react-native";

import { api } from "../api/client";
import { useAuthStore } from "../store/auth-store";
import { LoginResponse } from "../types/api";

export function LoginScreen() {
  const [email, setEmail] = useState("jose@example.com");
  const [password, setPassword] = useState("12345678");
  const setToken = useAuthStore((state) => state.setToken);

  const loginMutation = useMutation({
    mutationFn: async () => {
      const response = await api.post<LoginResponse>("/api/auth/login", {
        email,
        password,
      });

      return response.data;
    },
    onSuccess: async (data) => {
      await setToken(data.token);
    },
    onError: () => {
      Alert.alert("Erro", "Não foi possível autenticar. Verifique suas credenciais.");
    },
  });

  return (
    <View style={styles.container}>
      <Text style={styles.title}>Entrar no Portuguesando</Text>

      <TextInput
        autoCapitalize="none"
        keyboardType="email-address"
        onChangeText={setEmail}
        placeholder="Email"
        style={styles.input}
        value={email}
      />

      <TextInput
        onChangeText={setPassword}
        placeholder="Senha"
        secureTextEntry
        style={styles.input}
        value={password}
      />

      <Button
        disabled={loginMutation.isPending}
        onPress={() => loginMutation.mutate()}
        title={loginMutation.isPending ? "Entrando..." : "Entrar"}
      />
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flex: 1,
    justifyContent: "center",
    padding: 24,
    gap: 12,
  },
  title: {
    fontSize: 22,
    fontWeight: "700",
    marginBottom: 8,
  },
  input: {
    borderColor: "#d1d5db",
    borderRadius: 8,
    borderWidth: 1,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
});
