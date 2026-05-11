import "react-native-gesture-handler";

import { StatusBar } from "expo-status-bar";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import { useEffect } from "react";
import { ActivityIndicator, StyleSheet, View } from "react-native";
import { SafeAreaProvider } from "react-native-safe-area-context";

import { AppNavigator } from "./src/navigation/AppNavigator";
import { useAuthStore } from "./src/store/auth-store";

const queryClient = new QueryClient();

export default function App() {
  const initialize = useAuthStore((state) => state.initialize);
  const isHydrated = useAuthStore((state) => state.isHydrated);

  useEffect(() => {
    initialize();
  }, [initialize]);

  return (
    <QueryClientProvider client={queryClient}>
      <SafeAreaProvider>
        {isHydrated ? (
          <>
            <AppNavigator />
            <StatusBar style="auto" />
          </>
        ) : (
          <View style={styles.loadingContainer}>
            <ActivityIndicator size="large" />
          </View>
        )}
      </SafeAreaProvider>
    </QueryClientProvider>
  );
}

const styles = StyleSheet.create({
  loadingContainer: {
    flex: 1,
    alignItems: "center",
    justifyContent: "center",
  },
});
