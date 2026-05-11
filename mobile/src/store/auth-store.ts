import * as SecureStore from "expo-secure-store";
import { create } from "zustand";

const AUTH_TOKEN_KEY = "portuguesando_auth_token";

type AuthState = {
  token: string | null;
  isHydrated: boolean;
  setToken: (token: string) => Promise<void>;
  clearToken: () => Promise<void>;
  initialize: () => Promise<void>;
};

export const useAuthStore = create<AuthState>((set) => ({
  token: null,
  isHydrated: false,
  setToken: async (token) => {
    await SecureStore.setItemAsync(AUTH_TOKEN_KEY, token);
    set({ token });
  },
  clearToken: async () => {
    await SecureStore.deleteItemAsync(AUTH_TOKEN_KEY);
    set({ token: null });
  },
  initialize: async () => {
    const token = await SecureStore.getItemAsync(AUTH_TOKEN_KEY);
    set({ token, isHydrated: true });
  },
}));
