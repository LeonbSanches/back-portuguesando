import { useQuery } from "@tanstack/react-query";
import type { ReactNode } from "react";
import { Alert, Button, RefreshControl, ScrollView, StyleSheet, Text, View } from "react-native";

import { api } from "../api/client";
import { useAuthStore } from "../store/auth-store";
import { ProgressResponse } from "../types/api";

export function HomeScreen() {
  const clearToken = useAuthStore((state) => state.clearToken);

  const progressQuery = useQuery({
    queryKey: ["progress"],
    queryFn: async () => {
      const response = await api.get<ProgressResponse>("/api/me/progress");
      return response.data.data;
    },
  });

  const onLogout = async () => {
    try {
      await api.post("/api/auth/logout");
    } catch {
      // Token pode estar inválido; seguimos limpando sessão local.
    } finally {
      await clearToken();
    }
  };

  if (progressQuery.isError) {
    return (
      <View style={styles.container}>
        <Text style={styles.errorText}>Erro ao carregar dados da home.</Text>
        <Button onPress={() => progressQuery.refetch()} title="Tentar novamente" />
      </View>
    );
  }

  const progress = progressQuery.data;

  return (
    <ScrollView
      contentContainerStyle={styles.container}
      refreshControl={
        <RefreshControl refreshing={progressQuery.isRefetching} onRefresh={() => progressQuery.refetch()} />
      }
    >
      <Text style={styles.title}>Seu progresso</Text>

      {progress ? (
        <>
          <Card title="XP">
            <Text>Nível: {progress.xp.level}</Text>
            <Text>XP total: {progress.xp.total}</Text>
            <Text>Progresso p/ próximo nível: {progress.xp.progress_percent_to_next_level}%</Text>
          </Card>

          <Card title="Consistência">
            <Text>Score: {progress.consistency.score}</Text>
            <Text>Classificação: {progress.consistency.classification}</Text>
            <Text>Dias estudados (7d): {progress.consistency.study_days_last_7_days}</Text>
          </Card>

          <Card title="Próxima melhor ação">
            <Text>{progress.next_best_action.message}</Text>
            <Text>Prioridade: {progress.next_best_action.priority}</Text>
            <Button
              onPress={() =>
                Alert.alert(
                  progress.next_best_action.cta.label,
                  `Rota sugerida: ${progress.next_best_action.cta.route}`
                )
              }
              title={progress.next_best_action.cta.label}
            />
          </Card>
        </>
      ) : (
        <Text>Carregando...</Text>
      )}

      <View style={styles.logoutContainer}>
        <Button onPress={onLogout} title="Sair" />
      </View>
    </ScrollView>
  );
}

function Card({ title, children }: { title: string; children: ReactNode }) {
  return (
    <View style={styles.card}>
      <Text style={styles.cardTitle}>{title}</Text>
      <View style={styles.cardContent}>{children}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    padding: 16,
    gap: 12,
  },
  title: {
    fontSize: 24,
    fontWeight: "700",
  },
  card: {
    borderColor: "#e5e7eb",
    borderRadius: 10,
    borderWidth: 1,
    padding: 12,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: "700",
    marginBottom: 8,
  },
  cardContent: {
    gap: 4,
  },
  logoutContainer: {
    marginTop: 8,
  },
  errorText: {
    color: "#b91c1c",
    fontSize: 16,
    marginBottom: 8,
  },
});
